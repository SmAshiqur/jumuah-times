document.addEventListener("DOMContentLoaded", function () {
    const previewHorizontal = document.getElementById("preview-horizontal");
    const previewVertical = document.getElementById("preview-vertical");
    const modal = document.getElementById("widget-preview-modal");
    const modalContent = document.getElementById("widget-preview-content");
    const modalTitle = document.getElementById("widget-preview-title");
    const closeModal = document.getElementById("close-preview-modal");

    previewHorizontal.addEventListener("click", function () {
        modal.style.display = "flex";
        modalTitle.innerText = "Horizontal Widget Preview";
        fetchShortcode('[prayer_times_body_widget style="horizontal"]');
    });

    previewVertical.addEventListener("click", function () {
        modal.style.display = "flex";
        modalTitle.innerText = "Vertical Widget Preview";
        fetchShortcode('[prayer_times_body_widget style="vertical"]');
    });

    closeModal.addEventListener("click", function () {
        modal.style.display = "none";
        modalContent.innerHTML = ""; // Clear modal content
    });

    // Close modal when clicking outside the content
    modal.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
            modalContent.innerHTML = ""; // Clear modal content
        }
    });

    function fetchShortcode(shortcode) {
        fetch(PrayerTimesAdmin.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=render_shortcode&shortcode=${encodeURIComponent(shortcode)}`,
        })
            .then((response) => response.text()) // Read the response as plain text
            .then((data) => {
                modalContent.innerHTML = data; // Insert the raw HTML directly
            })
            .catch((error) => {
                modalContent.innerHTML = `<p style="color: red;">Error rendering shortcode: ${error.message}</p>`;
            });
    }
    
    
});
