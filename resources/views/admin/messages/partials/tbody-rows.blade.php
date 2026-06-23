@if($messages->count() > 0)
    <div class="msg-list">
        @foreach($messages as $message)
            @php
                $isUnread = !$message->is_read && $message->receiver_id === auth()->id();
                $typeMap = [
                    'operator_to_operator' => ['label' => 'مشغل لمشغل',  'cls' => 't-blue'],
                    'operator_to_staff'    => ['label' => 'مشغل لموظفين', 'cls' => 't-green'],
                    'admin_to_operator'    => ['label' => 'أدمن لمشغل',   'cls' => 't-amber'],
                    'admin_to_all'         => ['label' => 'أدمن للجميع',  'cls' => 't-red'],
                ];
                $ti = $typeMap[$message->type] ?? ['label' => $message->type, 'cls' => 't-blue'];

                $u = auth()->user();
                $isReceiver = $message->receiver_id === $u->id;
                $isBroadcastReceiver = false;
                if ($message->type === 'operator_to_staff' && $message->operator_id) {
                    if ($u->isCompanyOwner()) {
                        $isBroadcastReceiver = $u->ownedOperators()->where('id', $message->operator_id)->exists();
                    } elseif ($u->hasOperatorLinkedCustomRole()) {
                        $isBroadcastReceiver = optional($u->roleModel)->operator_id === $message->operator_id;
                    }
                }
                $showStatus = $isReceiver || $isBroadcastReceiver;
            @endphp
            <div class="msg-card {{ $isUnread ? 'is-unread' : '' }}" data-message-id="{{ $message->id }}">
                <span class="msg-ic"><i class="bi bi-envelope{{ $isUnread ? '-fill' : '' }}"></i></span>

                <div class="msg-main">
                    <div class="msg-top">
                        <a href="{{ route('admin.messages.show', $message) }}" class="msg-subject">{{ $message->subject }}</a>
                        @if($message->hasAttachment())
                            <i class="bi bi-paperclip msg-clip" title="يوجد مرفق"></i>
                        @endif
                        <span class="msg-type {{ $ti['cls'] }}">{{ $ti['label'] }}</span>
                        @if($isUnread)
                            <span class="msg-new">جديد</span>
                        @endif
                    </div>

                    <div class="msg-sub">
                        <span><i class="bi bi-person"></i> {{ $message->sender_display_name }}@if(!$message->isSystemMessage() && $message->sender) <span class="text-muted">({{ $message->sender->role_name }})</span>@endif</span>
                        <span class="sep">·</span>
                        <span><i class="bi bi-clock"></i> {{ $message->created_at->format('Y-m-d') }} {{ $message->created_at->format('H:i') }}</span>
                    </div>

                    <div class="msg-snippet">{{ Str::limit(strip_tags($message->body), 140) }}</div>
                </div>

                <div class="msg-end">
                    @if($showStatus)
                        @if($message->is_read)
                            <span class="msg-status read"><i class="bi bi-check2-all"></i> مقروء</span>
                        @else
                            <span class="msg-status unread"><i class="bi bi-dot"></i> غير مقروء</span>
                        @endif
                    @endif
                    <div class="msg-acts">
                        <a href="{{ route('admin.messages.show', $message) }}" class="btn btn-sm btn-light border" title="عرض">
                            <i class="bi bi-eye"></i>
                        </a>
                        @can('delete', $message)
                            <button type="button" class="btn btn-sm btn-light border btn-delete-message"
                                    data-id="{{ $message->id }}"
                                    data-url="{{ route('admin.messages.destroy', $message) }}"
                                    title="أرشفة">
                                <i class="bi bi-archive"></i>
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($messages->hasPages())
        <div class="msg-pagination mt-4">
            @include('admin.messages.partials.pagination', ['messages' => $messages])
        </div>
    @endif
@else
    <div class="msg-empty-state text-center py-5">
        <i class="bi bi-envelope-slash fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-muted">لا توجد رسائل</h5>
        <p class="text-muted">لم يتم العثور على رسائل تطابق البحث</p>
        @can('create', App\Models\Message::class)
            <a href="{{ route('admin.messages.create') }}" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle me-2"></i>
                إرسال رسالة جديدة
            </a>
        @endcan
    </div>
@endif
