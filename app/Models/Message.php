<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    /**
     * Customize route model binding to ensure security
     * Prevents access to messages deleted by sender or receiver
     * Note: Full authorization check is done in MessagePolicy and MessageController
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $message = parent::resolveRouteBinding($value, $field);
        
        // If message is soft deleted, return null (404)
        if (!$message) {
            return null;
        }
        
        // Check if message was deleted by sender or receiver
        // This prevents access to deleted messages even if they exist in database
        if (auth()->check()) {
            $user = auth()->user();
            
            $isSender = $message->sender_id === $user->id;
            $isReceiver = $message->receiver_id === $user->id;
            
            // If user is the sender and message was deleted by sender, return null (404)
            if ($isSender && $message->deleted_by_sender) {
                return null;
            }
            
            // If user is the receiver and message was deleted by receiver, return null (404)
            if ($isReceiver && $message->deleted_by_receiver) {
                return null;
            }
        }
        
        // Note: Full authorization (canBeViewedBy) is checked in MessageController::show()
        // This method only prevents access to messages deleted by the current user
        return $message;
    }

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'operator_id',
        'subject',
        'body',
        'attachment',
        'type',
        'forwarded_from_id',
        'is_cc',
        'is_bcc',
        'original_message_id',
        'is_read',
        'is_important',
        'is_starred',
        'read_at',
        'deleted_by_sender',
        'deleted_by_receiver',
        'is_archived',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'is_important' => 'boolean',
            'is_starred' => 'boolean',
            'is_cc' => 'boolean',
            'is_bcc' => 'boolean',
            'is_archived' => 'boolean',
            'read_at' => 'datetime',
            'archived_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_by_sender' => 'boolean',
            'deleted_by_receiver' => 'boolean',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    /**
     * الرسالة الأصلية (للرسائل المعاد توجيهها أو CC/BCC)
     */
    public function originalMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'original_message_id');
    }

    /**
     * الرسالة المعاد توجيهها منها
     */
    public function forwardedFrom(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'forwarded_from_id');
    }

    /**
     * الرسائل المعاد توجيهها من هذه الرسالة
     */
    public function forwardedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'forwarded_from_id');
    }

    /**
     * Get attachment URL if exists
     */
    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment ? asset('storage/' . $this->attachment) : null;
    }

    /**
     * Check if message has attachment
     */
    public function hasAttachment(): bool
    {
        return !empty($this->attachment);
    }

    /**
     * تحديد ما إذا كانت الرسالة موجهة لجميع موظفي المشغل
     */
    public function isBroadcastToStaff(): bool
    {
        return $this->type === 'operator_to_staff' && $this->receiver_id === null;
    }

    /**
     * تحديد ما إذا كانت الرسالة موجهة لجميع المشغلين
     */
    public function isBroadcastToOperators(): bool
    {
        return $this->type === 'admin_to_all' && $this->operator_id === null;
    }

    /**
     * Check if user can view this message
     * Each user can only see messages they sent or received
     */
    public function canBeViewedBy(User $user): bool
    {
        // Sender can always view their own messages
        if ($this->sender_id === $user->id) {
            return true;
        }

        // If message is sent to a specific user
        if ($this->receiver_id === $user->id) {
            return true;
        }

        // If message is broadcast to all staff of a specific operator
        if ($this->isBroadcastToStaff() && $this->operator_id) {
            if ($user->isCompanyOwner()) {
                return $user->ownedOperators()->where('id', $this->operator_id)->exists();
            }
            // Check if user has custom role linked to this operator
            if ($user->hasOperatorLinkedCustomRole()) {
                return $user->roleModel->operator_id === $this->operator_id;
            }
        }

        // If message is sent to a specific operator (from admin)
        if ($this->type === 'admin_to_operator' && $this->operator_id) {
            if ($user->isCompanyOwner()) {
                return $user->ownedOperators()->where('id', $this->operator_id)->exists();
            }
        }

        // If message is broadcast to all operators (from admin)
        if ($this->isBroadcastToOperators()) {
            return $user->isCompanyOwner();
        }

        return false;
    }

    /**
     * تحديد ما إذا كان المستخدم يمكنه الرد على الرسالة
     */
    public function canBeRepliedBy(User $user): bool
    {
        if (!$this->canBeViewedBy($user)) {
            return false;
        }

        // لا يمكن الرد على الرسائل الموجهة للجميع
        if ($this->isBroadcastToStaff() || $this->isBroadcastToOperators()) {
            return false;
        }

        // المرسل يمكنه الرد
        if ($this->sender_id === $user->id) {
            return true;
        }

        // المستقبل يمكنه الرد
        if ($this->receiver_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Get sender display name
     * For system messages (from platform_rased user), show site name instead of user name
     */
    public function getSenderDisplayNameAttribute(): string
    {
        // Check if this is a system message (from platform_rased user)
        if ($this->isSystemMessage()) {
            return 'منصة ' . \App\Models\Setting::get('site_name', 'نور');
        }

        // For regular messages, return sender name
        return $this->sender ? $this->sender->name : 'غير معروف';
    }

    /**
     * Check if this is a system message (from platform_rased user)
     */
    public function isSystemMessage(): bool
    {
        // Check if sender is system user (platform_rased)
        return $this->sender && $this->sender->isSystemUser();
    }
}
