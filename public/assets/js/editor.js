tinymce.init({
    selector: '.tinymce-editor',

    plugins: 'image link lists code',
    toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | image link',

    automatic_uploads: true,
    images_upload_handler: function (blobInfo, success, failure) {

        const formData = new FormData();
        formData.append('file', blobInfo.blob(), blobInfo.filename());

        fetch('/task/upload-image', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.location) {
                    success(data.location); // 👈 URL ảnh
                } else {
                    failure('Upload failed');
                }
            })
            .catch(() => {
                failure('Upload error');
            });
    }
});