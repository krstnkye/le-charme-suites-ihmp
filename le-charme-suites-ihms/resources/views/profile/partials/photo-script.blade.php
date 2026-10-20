<script>
    document.querySelectorAll('[data-profile-photo-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const file = input.files?.[0];

            if (!file) {
                return;
            }

            document.querySelectorAll('[data-profile-photo-input]').forEach((otherInput) => {
                if (otherInput !== input) {
                    otherInput.value = '';
                }
            });

            const previewUrl = URL.createObjectURL(file);
            document.querySelectorAll('[data-profile-photo-preview]').forEach((preview) => {
                preview.src = previewUrl;
                preview.classList.remove('hidden');
            });
            document.querySelectorAll('[data-profile-photo-placeholder]').forEach((placeholder) => {
                placeholder.classList.add('hidden');
                placeholder.classList.remove('flex');
            });
            document.querySelectorAll('[data-profile-photo-status]').forEach((status) => {
                status.classList.remove('hidden');
            });
        });
    });
</script>
