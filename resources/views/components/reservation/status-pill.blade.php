@props(['status', 'plain' => false, 'userFacing' => false, 'colored' => true])

<x-ui.badge :status="$status" :plain="$plain" :colored="$plain && $colored" {{ $attributes->class(['text-xs font-extrabold' => $plain]) }}>{{ $userFacing && $status === 'cancelled_by_system' ? 'Gagal' : \App\Models\Reservation::statusLabel($status) }}</x-ui.badge>
