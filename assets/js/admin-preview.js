document.addEventListener("DOMContentLoaded", function () {
    const previewHorizontal = document.getElementById("preview-horizontal");
    const previewVertical = document.getElementById("preview-vertical");
    const previewArea = document.getElementById("widget-preview-area");
    const previewContent = document.getElementById("widget-preview-content");

    previewHorizontal.addEventListener("click", function () {
        previewArea.style.display = "block";
        previewContent.innerHTML = '<h3>Horizontal View</h3>' + doShortcode('[prayer_times_body_widget style="horizontal"]');
    });

    previewVertical.addEventListener("click", function () {
        previewArea.style.display = "block";
        previewContent.innerHTML = '<h3>Vertical View</h3>' + doShortcode('[prayer_times_body_widget style="vertical"]');
    });

    function doShortcode(shortcode) {
        // Simulate shortcode rendering (for dynamic previewing in admin)
        // Replace this with an AJAX call if server-side rendering is required.
        if (shortcode.includes("horizontal")) {
            return `<div style="text-align:center; padding:20px;">This is the Horizontal Widget</div>`;
        } else if (shortcode.includes("vertical")) {
            return `<div style="text-align:center; padding:20px;">This is the Vertical Widget</div>`;
        }
    }
});
