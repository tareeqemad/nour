<?php

namespace App;

enum Governorate: int
{
    case NorthGaza = 10;
    case Gaza = 20;
    case Middle = 30;
    case KhanYunis = 40;
    case Rafah = 50;

    /**
     * الحصول على اسم المحافظة بالعربية
     */
    public function label(): string
    {
        return match ($this) {
            self::NorthGaza => 'شمال غزة',
            self::Gaza => 'غزة',
            self::Middle => 'الوسطى',
            self::KhanYunis => 'خانيونس',
            self::Rafah => 'رفح',
        };
    }

    /**
     * الحصول على ترميز المحافظة
     */
    public function code(): string
    {
        return match ($this) {
            self::NorthGaza => 'NG',
            self::Gaza => 'GZ',
            self::Middle => 'MD',
            self::KhanYunis => 'KH',
            self::Rafah => 'RF',
        };
    }

    /**
     * الحصول على جميع المحافظات
     */
    public static function all(): array
    {
        return self::cases();
    }

    /**
     * الحصول على المحافظة من الرقم
     */
    public static function fromValue(int $value): ?self
    {
        return self::tryFrom($value);
    }

    /**
     * الحصول على تفاصيل المحافظة
     */
    public function details(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
            'code' => $this->code(),
        ];
    }
}
