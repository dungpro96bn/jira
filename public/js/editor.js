export function initEditor(selector, onChange) {
    tinymce.init({
        selector,
        height: 400,
        menubar: false,
        plugins: 'image advlist autolink lists link charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
        toolbar: 'bold italic backcolor | undo redo | formatselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | image | help',

        // =========================
        automatic_uploads: true,
        images_upload_url: '/task/upload-image',

        images_upload_handler: function (blobInfo) {

            return new Promise((resolve, reject) => {

                const formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());

                fetch('/task/upload-image', {
                    method: 'POST',
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {

                        if (data.location) {
                            resolve(data.location);
                        } else {
                            reject('Upload failed');
                        }

                    })
                    .catch(() => {
                        reject('Upload error');
                    });

            });
        },

        setup: function (editor) {
            editor.on('init', () => {
                const content = editor.getContent();
                document.querySelector('#description').value = content;
            });

            editor.on('keyup change paste', () => {
                const content = editor.getContent();
                document.querySelector('#description').value = content;

                if (onChange) onChange(content);
            });
        }
    });
}