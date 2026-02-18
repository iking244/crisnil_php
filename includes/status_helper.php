<?php
function renderStatusBadge($status)
{

    // Map database status → display text
    $statusLabels = [
        'pending' => 'Pending',
        'pending_loading' => 'Pending Loading',
        'loading' => 'Ongoing Loading',
        'assigned' => 'Assigned',
        'in_transit' => 'In Transit',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled'
    ];

    // Get display text
    $displayStatus = $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status));

    // Return the badge HTML
    return '<span class="status-badge ' . $status . '">' . $displayStatus . '</span>';
}
