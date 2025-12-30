<?php

namespace App\Observers;

use App\Models\DanceService;

/**
 * Observer untuk DanceService
 * 
 * NOTE: Jasa tari adalah service-based, bukan inventory-based.
 * Dia tidak menggunakan stock/slot system.
 * Status hanya: tersedia, tidak tersedia (by admin), atau dipesan.
 * 
 * Tidak perlu stock tracking untuk jasa tari.
 */
class DanceServiceObserver
{
    // Empty observer - jasa tari tidak perlu stock syncing
}

