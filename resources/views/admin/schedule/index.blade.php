@extends('layouts.admin')

@section('title', 'Jadwal & Kalender')

@section('page-title')
    <div class="flex items-center">
        Jadwal & Kalender Booking
    </div>
@endsection

@section('content')
<!-- Filter -->
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex items-center gap-4">
            <label class="text-sm font-medium text-gray-700">Menampilkan:</label>
            <p class="text-sm text-gray-600">Semua Layanan (Kostum, Jasa Tari, Jasa Rias)</p>
        </div>
    </div>

    <!-- Calendar -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div id="calendar"></div>
    </div>

<!-- Event Detail Modal -->
<div id="eventModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <div>
                <h3 class="text-xl font-semibold text-gray-900">Pesanan pada <span id="modalDate"></span></h3>
                <p class="text-sm text-gray-500 mt-1" id="orderCount"></p>
            </div>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="eventDetails" class="max-h-96 overflow-y-auto"></div>
    </div>
</div>

<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />

@push('scripts')
<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    // Get dates with orders from server (passed from controller)
    var datesWithOrders = {!! json_encode($datesWithOrders ?? []) !!};
    var ordersWithDateHighlight = new Map();
    
    // Build map from server data
    datesWithOrders.forEach(date => {
        ordersWithDateHighlight.set(date, true);
    });
    
    // Load booked dates summary
    loadBookedDatesSummary();
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: function(info, successCallback, failureCallback) {
            // Always fetch all events without filter (showing semua layanan)
            var url = '{{ route('admin.schedule.events') }}?start=' + info.startStr + '&end=' + info.endStr;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    successCallback(data);
                })
                .catch(error => {
                    console.error('Error loading events:', error);
                    failureCallback(error);
                });
        },
        dateClick: function(info) {
            // When user click on a date, show modal with all orders for that date
            showOrdersForDate(info.dateStr);
        },
        eventClick: function(info) {
            // When user click on an event, show single event details
            showEventDetails(info.event);
        },
        eventContent: function(arg) {
            return {
                html: '<div class="px-2 py-1 text-xs font-medium truncate">' + arg.event.title + '</div>'
            };
        },
        dayCellDidMount: function(info) {
            // Highlight dates that have orders
            const dateStr = info.date.toISOString().split('T')[0];
            if (ordersWithDateHighlight.has(dateStr)) {
                // Add blue background and styling
                info.el.style.backgroundColor = '#dbeafe'; // light blue
                info.el.style.borderLeft = '4px solid #3b82f6'; // blue border
                info.el.style.fontWeight = 'bold';
                
                // Also highlight the day number
                const dayNumber = info.el.querySelector('.fc-daygrid-day-number');
                if (dayNumber) {
                    dayNumber.style.backgroundColor = '#3b82f6';
                    dayNumber.style.color = 'white';
                    dayNumber.style.padding = '4px 8px';
                    dayNumber.style.borderRadius = '4px';
                }
            }
        }
    });
    
    calendar.render();
    
    // Load booked dates summary
    function loadBookedDatesSummary() {
        fetch('{{ route('admin.schedule.booked-dates') }}')
            .then(response => response.json())
            .then(data => {
                displayBookedDates('costumeBookedDates', data.costume, 'Kostum');
                displayBookedDates('danceBookedDates', data.dance, 'Jasa Tari');
                displayBookedDates('makeupBookedDates', data.makeup, 'Jasa Rias');
            })
            .catch(error => console.error('Error loading booked dates:', error));
    }
    
    function displayBookedDates(elementId, dates, label) {
        const container = document.getElementById(elementId);
        
        if (!dates || dates.length === 0) {
            container.innerHTML = '<p class="text-gray-500 italic">Tidak ada yang dipesan</p>';
            return;
        }
        
        // Group dates by month
        const grouped = {};
        dates.forEach(date => {
            const dateObj = new Date(date);
            const monthYear = dateObj.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
            
            if (!grouped[monthYear]) {
                grouped[monthYear] = [];
            }
            grouped[monthYear].push(dateObj.toLocaleDateString('id-ID', { day: '2-digit' }));
        });
        
        let html = '';
        Object.entries(grouped).forEach(([monthYear, dayList]) => {
            html += `
                <div class="mb-2">
                    <p class="text-xs font-semibold text-gray-700">${monthYear}</p>
                    <p class="text-xs text-gray-600">${dayList.join(', ')}</p>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
    
    // Show all orders for a specific date in modal
    window.showOrdersForDate = function(dateStr) {
        // Format date for display
        const dateObj = new Date(dateStr + 'T00:00:00');
        const formattedDate = dateObj.toLocaleDateString('id-ID', { 
            weekday: 'long',
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        // Fetch orders for this date
        fetch('{{ route('admin.schedule.orders-by-date') }}?date=' + dateStr)
            .then(response => response.json())
            .then(data => {
                document.getElementById('modalDate').textContent = formattedDate;
                document.getElementById('orderCount').textContent = 'Total ' + data.orders.length + ' pesanan';
                
                if (data.orders.length === 0) {
                    document.getElementById('eventDetails').innerHTML = '<p class="text-gray-500 text-center py-4">Tidak ada pesanan pada tanggal ini</p>';
                    document.getElementById('eventModal').classList.remove('hidden');
                    return;
                }
                
                // Build HTML for all orders
                let html = '<div class="space-y-4">';
                
                data.orders.forEach(order => {
                    const statusColor = {
                        'pending': 'yellow',
                        'paid': 'yellow',
                        'confirmed': 'blue',
                        'processing': 'purple',
                        'ready': 'green',
                        'completed': 'gray',
                        'expired': 'orange'
                    };
                    
                    const statusBadge = `<span class="px-2 py-1 text-xs font-semibold rounded bg-${statusColor[order.status] || 'gray'}-100 text-${statusColor[order.status] || 'gray'}-800">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span>`;
                    
                    const itemsHtml = order.items.map(item => {
                        const typeLabel = {
                            'kostum': 'Kostum',
                            'tari': 'Jasa Tari',
                            'rias': 'Jasa Rias'
                        };
                        return `
                            <tr class="border-t text-sm">
                                <td class="px-4 py-2 text-gray-700">${item.name}</td>
                                <td class="px-4 py-2 text-gray-500">${typeLabel[item.type] || item.type}</td>
                                <td class="px-4 py-2 text-center text-gray-700">${item.quantity}</td>
                                <td class="px-4 py-2 text-right text-gray-700">Rp ${new Intl.NumberFormat('id-ID').format(item.unit_price)}</td>
                                <td class="px-4 py-2 text-right text-gray-900 font-semibold">Rp ${new Intl.NumberFormat('id-ID').format(item.total_price)}</td>
                            </tr>
                        `;
                    }).join('');
                    
                    html += `
                        <div class="border rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="font-semibold text-gray-900">${order.order_code}</h4>
                                    <p class="text-sm text-gray-600">${order.customer_name} (${order.customer_email})</p>
                                </div>
                                <div class="text-right">
                                    ${statusBadge}
                                    <p class="text-sm text-gray-500 mt-1">${order.return_status === 'belum' ? 'Belum Dikembalikan' : order.return_status === 'sudah' ? 'Sudah Dikembalikan' : 'Terlambat'}</p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-4 gap-2 mb-3 text-sm">
                                <div>
                                    <p class="text-gray-500">Tanggal Mulai</p>
                                    <p class="font-semibold text-gray-900">${new Date(order.start_date).toLocaleDateString('id-ID')}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Tanggal Selesai</p>
                                    <p class="font-semibold text-gray-900">${new Date(order.end_date).toLocaleDateString('id-ID')}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Status Pembayaran</p>
                                    <p class="font-semibold text-gray-900">${order.status === 'paid' || order.status === 'completed' ? 'Lunas' : 'Belum Lunas'}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-gray-500">Total Harga</p>
                                    <p class="font-bold text-green-600">Rp ${new Intl.NumberFormat('id-ID').format(order.total_price)}</p>
                                </div>
                            </div>
                            
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Nama Item</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Jenis</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-700">Qty</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Harga Satuan</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${itemsHtml}
                                </tbody>
                            </table>
                            
                            <div class="flex justify-end gap-2 mt-3 border-t pt-3">
                                <a href="/admin/orders/${order.id}" class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                document.getElementById('eventDetails').innerHTML = html;
                document.getElementById('eventModal').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error loading orders:', error);
                document.getElementById('eventDetails').innerHTML = '<p class="text-red-500">Gagal memuat data pesanan</p>';
                document.getElementById('eventModal').classList.remove('hidden');
            });
    };
    
    // Show single event details (when clicking on event in calendar)
    window.showEventDetails = function(event) {
        var props = event.extendedProps;
        var statusBadge = {
            'pending': '<span class="px-2 py-1 text-xs font-semibold rounded bg-yellow-100 text-yellow-800">Pending</span>',
            'confirmed': '<span class="px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">Confirmed</span>',
            'processing': '<span class="px-2 py-1 text-xs font-semibold rounded bg-purple-100 text-purple-800">Processing</span>',
            'ready': '<span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">Ready</span>',
            'completed': '<span class="px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800">Completed</span>',
            'expired': '<span class="px-2 py-1 text-xs font-semibold rounded bg-orange-100 text-orange-800">Expired</span>'
        };
        
        var itemsHtml = props.items.map(item => {
            var typeLabel = {
                'costume': 'Kostum',
                'dance': 'Jasa Tari',
                'makeup': 'Jasa Rias'
            };
            
            return `
                <tr>
                    <td class="px-4 py-2 text-sm text-gray-700">${item.name}</td>
                    <td class="px-4 py-2 text-sm text-gray-500">${typeLabel[item.type]}</td>
                    <td class="px-4 py-2 text-sm text-center text-gray-700">${item.quantity}</td>
                    <td class="px-4 py-2 text-sm text-right text-gray-700">Rp ${new Intl.NumberFormat('id-ID').format(item.price)}</td>
                </tr>
            `;
        }).join('');
        
        var html = `
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Kode Pesanan</p>
                        <p class="font-semibold text-gray-900">${props.order_code}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <p class="mt-1">${statusBadge[props.status]}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Customer</p>
                        <p class="font-semibold text-gray-900">${props.customer}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total</p>
                        <p class="font-semibold text-green-600">Rp ${new Intl.NumberFormat('id-ID').format(props.total)}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Tanggal Mulai</p>
                        <p class="font-semibold text-gray-900">${new Date(event.start).toLocaleDateString('id-ID', {day: '2-digit', month: 'long', year: 'numeric'})}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Tanggal Selesai</p>
                        <p class="font-semibold text-gray-900">${new Date(event.end).toLocaleDateString('id-ID', {day: '2-digit', month: 'long', year: 'numeric'})}</p>
                    </div>
                </div>
                
                <div class="border-t pt-4">
                    <h4 class="font-semibold text-gray-900 mb-3">Item Pesanan:</h4>
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Nama Item</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700">Jenis</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-700">Qty</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700">Harga</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            ${itemsHtml}
                        </tbody>
                    </table>
                </div>
                
                <div class="flex justify-end gap-2 border-t pt-4">
                    <a href="/admin/orders/${event.id}" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Lihat Detail Lengkap
                    </a>
                </div>
            </div>
        `;
        
        document.getElementById('eventDetails').innerHTML = html;
        document.getElementById('eventModal').classList.remove('hidden');
    };
    
    window.closeModal = function() {
        document.getElementById('eventModal').classList.add('hidden');
    };
});
</script>
@endpush
@endsection
