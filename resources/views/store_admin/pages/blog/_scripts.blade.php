<script src="{{ asset('backend/assets/libs/@ckeditor/ckeditor5-build-classic/build/ckeditor.js') }}"></script>
<script>
    (function () {
        var editorElement = document.getElementById('blog-long-desc');

        if (!editorElement || typeof ClassicEditor === 'undefined') {
            return;
        }

        ClassicEditor
            .create(editorElement, {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo'],
            })
            .catch(function (error) {
                console.error(error);
            });
    })();
</script>
