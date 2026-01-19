<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TracksUser;

class OperationLog extends Model
{
    use SoftDeletes, TracksUser;

    protected $fillable = [
        'generator_id',
        'operator_id',
        'sequence',
        'operation_date',
        'start_time',
        'end_time',
        'load_percentage',
        'fuel_meter_start',
        'fuel_meter_end',
        'fuel_consumed',
        'energy_meter_start',
        'energy_meter_end',
        'energy_produced',
        'electricity_tariff_price',
        'operational_notes',
        'malfunctions',
    ];

    protected function casts(): array
    {
        return [
            'operation_date' => 'date',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'load_percentage' => 'decimal:2',
            'fuel_meter_start' => 'decimal:2',
            'fuel_meter_end' => 'decimal:2',
            'fuel_consumed' => 'decimal:2',
            'energy_meter_start' => 'decimal:2',
            'energy_meter_end' => 'decimal:2',
            'energy_produced' => 'decimal:2',
            'electricity_tariff_price' => 'decimal:4',
        ];
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(Generator::class)->withTrashed();
    }

    /**
     * العلاقة مع المولد (بما في ذلك المحذوفة soft delete)
     * للاستخدام في السجلات التاريخية
     */
    public function generatorWithTrashed(): BelongsTo
    {
        return $this->belongsTo(Generator::class)->withTrashed();
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    /**
     * الحصول على الرقم التسلسلي المنسق: أول كلمة من اسم المولد (بالإنجليزية) + O001
     */
    public function getFormattedSequenceAttribute(): string
    {
        if (!$this->sequence) {
            return '#' . $this->id;
        }

        $generator = $this->generator;
        if (!$generator || !$generator->name) {
            return 'O' . str_pad($this->sequence, 3, '0', STR_PAD_LEFT);
        }

        // أخذ أول كلمة من اسم المولد
        $name = trim($generator->name);
        $words = preg_split('/[\s\-_]+/', $name);
        $firstWord = $words[0] ?? '';
        
        // تحويل أول حرف إلى إنجليزية إذا كان عربي
        $firstChar = mb_substr($firstWord, 0, 1, 'UTF-8');
        $englishPrefix = $this->getEnglishPrefix($firstChar);
        
        // الرقم التسلسلي بصيغة O000
        $sequenceFormatted = 'O' . str_pad($this->sequence, 3, '0', STR_PAD_LEFT);
        
        return strtoupper($englishPrefix) . '-' . $sequenceFormatted;
    }

    /**
     * تحويل أول حرف إلى حرف إنجليزي
     */
    private function getEnglishPrefix(string $char): string
    {
        // إذا كان الحرف إنجليزي، نرجعه كما هو
        if (preg_match('/^[A-Za-z]$/', $char)) {
            return strtoupper($char);
        }
        
        // إذا كان حرف عربي، نحوله إلى حرف إنجليزي
        $arabicToEnglish = [
            'أ' => 'A', 'ا' => 'A', 'إ' => 'I', 'آ' => 'A',
            'ب' => 'B', 'ت' => 'T', 'ث' => 'TH', 'ج' => 'J',
            'ح' => 'H', 'خ' => 'KH', 'د' => 'D', 'ذ' => 'DH',
            'ر' => 'R', 'ز' => 'Z', 'س' => 'S', 'ش' => 'SH',
            'ص' => 'S', 'ض' => 'D', 'ط' => 'T', 'ظ' => 'Z',
            'ع' => 'A', 'غ' => 'GH', 'ف' => 'F', 'ق' => 'Q',
            'ك' => 'K', 'ل' => 'L', 'م' => 'M', 'ن' => 'N',
            'ه' => 'H', 'و' => 'W', 'ي' => 'Y', 'ى' => 'Y',
        ];
        
        return $arabicToEnglish[$char] ?? 'G';
    }
}
