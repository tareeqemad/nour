@if($messages->count() > 0)
    <div class="msg-list">
        @foreach($messages as $message)
            <div class="msg-row {{ !$message->is_read && $message->receiver_id === auth()->id() ? 'msg-unread' : '' }}" data-message-id="{{ $message->id }}">
                <div class="msg-row-main">
                    <div class="msg-row-content">
                        <div class="msg-row-header">
                            <div class="msg-row-title">
                                <i class="bi bi-envelope me-2 text-primary"></i>
                                <span class="fw-bold">{{ $message->subject }}</span>
                                @if($message->hasAttachment())
                                    <i class="bi bi-image text-success ms-2" title="يوجد صورة مرفقة"></i>
                                @endif
                                @if(!$message->is_read && $message->receiver_id === auth()->id())
                                    <span class="badge bg-primary ms-2 pulse">جديد</span>
                                @endif
                            </div>
                            <div class="msg-row-meta">
                                @php
                                    $typeLabels = [
                                        'operator_to_operator' => ['label' => 'مشغل لمشغل', 'badge' => 'bg-primary'],
                                        'operator_to_staff' => ['label' => 'مشغل لموظفين', 'badge' => 'bg-success'],
                                        'admin_to_operator' => ['label' => 'أدمن لمشغل', 'badge' => 'bg-warning'],
                                        'admin_to_all' => ['label' => 'أدمن للجميع', 'badge' => 'bg-danger'],
                                    ];
                                    $typeInfo = $typeLabels[$message->type] ?? ['label' => $message->type, 'badge' => 'bg-secondary'];
                                @endphp
                                <span class="badge {{ $typeInfo['badge'] }}">{{ $typeInfo['label'] }}</span>
                            </div>
                        </div>

                        <div class="msg-row-details">
                            <div class="row g-2">
                                <div class="col-md-3 col-sm-6">
                                    <div class="msg-detail-item">
                                        <i class="bi bi-person me-2 text-muted"></i>
                                        <span class="text-muted">المرسل:</span>
                                        <strong>{{ $message->sender_display_name }}</strong>
                                        @if(!$message->isSystemMessage() && $message->sender)
                                            <small class="text-muted">({{ $message->sender->role_name }})</small>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-6">
                                    <div class="msg-detail-item">
                                        <i class="bi bi-person-check me-2 text-muted"></i>
                                        <span class="text-muted">المستقبل:</span>
                                        @if($message->receiver)
                                            <strong>{{ $message->receiver->name }}</strong>
                                            <small class="text-muted">({{ $message->receiver->role_name }})</small>
                                        @elseif($message->operator)
                                            <strong>{{ $message->operator->name }}</strong>
                                            <small class="text-muted">(مشغل)</small>
                                        @else
                                            <strong>
                                                @if($message->type === 'admin_to_all')
                                                    جميع المشغلين
                                                @elseif($message->type === 'operator_to_staff')
                                                    جميع الموظفين
                                                @else
                                                    -
                                                @endif
                                            </strong>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-6">
                                    <div class="msg-detail-item">
                                        <i class="bi bi-calendar3 me-2 text-muted"></i>
                                        <span class="text-muted">التاريخ:</span>
                                        <strong>{{ $message->created_at->format('Y-m-d') }}</strong>
                                        <small class="text-muted">({{ $message->created_at->format('H:i') }})</small>
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-6">
                                    <div class="msg-detail-item">
                                        <i class="bi bi-eye me-2 text-muted"></i>
                                        <span class="text-muted">الحالة:</span>
                                        @php
                                            $user = auth()->user();
                                            $isReceiver = $message->receiver_id === $user->id;
                                            $isBroadcastReceiver = false;
                                            if ($message->type === 'operator_to_staff' && $message->operator_id) {
                                                if ($user->isCompanyOwner()) {
                                                    $isBroadcastReceiver = $user->ownedOperators()->where('id', $message->operator_id)->exists();
                                                } elseif ($user->hasOperatorLinkedCustomRole()) {
                                                    $isBroadcastReceiver = $user->roleModel->operator_id === $message->operator_id;
                                                }
                                            }
                                        @endphp
                                        @if($isReceiver || $isBroadcastReceiver)
                                            @if($message->is_read)
                                                <span class="badge bg-success">مقروء</span>
                                            @else
                                                <span class="badge bg-warning">غير مقروء</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="msg-preview mt-2">
                                <p class="text-muted mb-0 small">
                                    <i class="bi bi-chat-left-text me-1"></i>
                                    {{ Str::limit(strip_tags($message->body), 100) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="msg-row-actions">
                        <a href="{{ route('admin.messages.show', $message) }}" class="btn btn-sm btn-outline-info" title="عرض">
                            <i class="bi bi-eye"></i>
                        </a>
                        @can('delete', $message)
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-message" 
                                    data-id="{{ $message->id }}"
                                    data-url="{{ route('admin.messages.destroy', $message) }}"
                                    title="حذف">
                                <i class="bi bi-trash"></i>
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
