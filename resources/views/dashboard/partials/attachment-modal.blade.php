<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">Добавить медиа</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ $route }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <input type="file" name="media[]" multiple class="form-control-file">
                    </div>
                    <div class="form-group mt-3">
                        <label for="commentField{{ $uniqueId ?? '' }}">Комментарий</label>
                        <textarea name="comment" class="form-control" id="commentField{{ $uniqueId ?? '' }}" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Сохранить медиа</button>
                </form>
            </div>
        </div>
    </div>
</div> 