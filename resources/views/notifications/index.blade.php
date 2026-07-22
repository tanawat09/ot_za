@extends('layouts.app')

@section('title', 'การแจ้งเตือน')
@section('header', 'ศูนย์การแจ้งเตือน (Notifications)')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card card-custom p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h5 class="fw-bold font-heading mb-0">
                    <i class="bi bi-bell text-primary me-2"></i>การแจ้งเตือนของคุณ
                </h5>
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-check-all me-1"></i> ทำเป็นอ่านแล้วทั้งหมด
                    </button>
                </form>
            </div>

            <div class="list-group list-group-flush">
                @forelse($notifications as $notif)
                    <a href="{{ route('notifications.read', $notif) }}" class="list-group-item list-group-item-action p-3 {{ $notif->is_read ? 'bg-white' : 'bg-primary-subtle' }} rounded mb-2 border">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div class="fw-bold text-dark fs-6">{{ $notif->title }}</div>
                            <span class="fs-7 text-muted">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-secondary fs-7">{{ $notif->message }}</div>
                    </a>
                @empty
                    <div class="text-center py-4 text-muted">ยังไม่มีการแจ้งเตือนในขณะนี้</div>
                @endforelse
            </div>

            <div class="mt-3">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
