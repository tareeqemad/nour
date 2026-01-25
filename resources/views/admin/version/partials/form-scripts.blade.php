{{-- 
    Partial للـ JavaScript المشترك في نماذج الإصدارات
    المتغيرات المطلوبة:
    - $nextVersions (اختياري): أرقام الإصدارات التالية (للإنشاء فقط)
--}}

<script>
    (function($) {
        $(document).ready(function() {
            @if(isset($nextVersions))
            // تحديث رقم الإصدار عند تغيير النوع (فقط في صفحة الإنشاء)
            const versionType = $('#versionType');
            const versionNumber = $('#versionNumber');
            const versions = {
                patch: '{{ $nextVersions["patch"] ?? "" }}',
                minor: '{{ $nextVersions["minor"] ?? "" }}',
                major: '{{ $nextVersions["major"] ?? "" }}'
            };
            
            versionType.on('change', function() {
                versionNumber.val(versions[$(this).val()]);
            });
            @endif
            
            // إضافة حقول جديدة
            $(document).on('click', '.add-field', function() {
                const target = $(this).data('target');
                const container = $(`#${target}-container`);
                const btnClass = $(this).attr('class').match(/btn-outline-\w+/)[0];
                
                const newField = `
                    <div class="input-group mb-2">
                        <input type="text" name="${target}[]" class="form-control" 
                               placeholder="أضف عنصراً جديداً...">
                        <button type="button" class="btn btn-outline-danger remove-field">
                            <i class="bi bi-dash-lg"></i>
                        </button>
                    </div>
                `;
                
                // إضافة قبل حقل الإضافة الأخير
                $(this).closest('.input-group').before(newField);
            });
            
            // حذف حقل
            $(document).on('click', '.remove-field', function() {
                $(this).closest('.input-group').remove();
            });
        });
    })(jQuery);
</script>
