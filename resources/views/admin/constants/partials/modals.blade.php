@foreach($constants as $constant)
    @can('delete', $constant)
        <div class="modal fade" id="deleteModal{{ $constant->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">تأكيد الحذف</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>هل أنت متأكد من حذف الثابت <strong>{{ $constant->constant_name }}</strong>؟</p>
                        @if($constant->all_details_count > 0)
                            <p class="text-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <small>هذا الثابت يحتوي على {{ $constant->all_details_count }} تفصيل. سيتم حذف جميع التفاصيل المرتبطة به.</small>
                            </p>
                        @endif
                        <p class="text-danger"><small>هذا الإجراء لا يمكن التراجع عنه</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <form action="{{ route('admin.constants.destroy', $constant) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">حذف</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endforeach












