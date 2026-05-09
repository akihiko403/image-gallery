document.addEventListener('DOMContentLoaded', function() {
    // File upload preview
    const fileInput = document.getElementById('image');
    const fileInfo = document.getElementById('fileInfo');
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                const fileName = this.files[0].name;
                const fileSize = (this.files[0].size / 1024 / 1024).toFixed(2); // MB
                fileInfo.textContent = `${fileName} (${fileSize} MB)`;
                fileInfo.style.color = '#4361ee';
            } else {
                fileInfo.textContent = 'No file selected';
                fileInfo.style.color = '#6c757d';
            }
        });
    }
    
    // Upload form submission with progress indicator
    const uploadForm = document.getElementById('uploadForm');
    const progressBar = document.getElementById('progressBar');
    const uploadProgress = document.getElementById('uploadProgress');
    
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const xhr = new XMLHttpRequest();
            
            // Show progress bar
            uploadProgress.style.display = 'block';
            progressBar.style.width = '0%';
            
            // Upload progress
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progressBar.style.width = percentComplete + '%';
                }
            });
            
            // Request completed
            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (response.success) {
                        // Show success message
                        showNotification('Image uploaded successfully!', 'success');
                        
                        // Reset form
                        uploadForm.reset();
                        fileInfo.textContent = 'No file selected';
                        fileInfo.style.color = '#6c757d';
                        
                        // Reload page after 1.5 seconds to show new image
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showNotification(response.message, 'error');
                    }
                } else {
                    showNotification('Upload failed. Please try again.', 'error');
                }
                
                // Hide progress bar
                setTimeout(() => {
                    uploadProgress.style.display = 'none';
                    progressBar.style.width = '0%';
                }, 1000);
            });
            
            // Request error
            xhr.addEventListener('error', function() {
                showNotification('Network error. Please check your connection.', 'error');
                uploadProgress.style.display = 'none';
            });
            
            // Send request
            xhr.open('POST', 'upload.php');
            xhr.send(formData);
        });
    }
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const categoryFilter = document.getElementById('categoryFilter');
    const resetFilter = document.getElementById('resetFilter');
    
    function applyFilters() {
        const search = searchInput.value.trim();
        const category = categoryFilter.value;
        
        let url = 'index.php?';
        if (search) url += `search=${encodeURIComponent(search)}&`;
        if (category !== 'all') url += `category=${encodeURIComponent(category)}`;
        
        // Remove trailing & or ? if no parameters
        if (url.endsWith('&') || url.endsWith('?')) {
            url = url.slice(0, -1);
        }
        
        window.location.href = url;
    }
    
    if (searchBtn) {
        searchBtn.addEventListener('click', applyFilters);
    }
    
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', applyFilters);
    }
    
    if (resetFilter) {
        resetFilter.addEventListener('click', function() {
            window.location.href = 'index.php';
        });
    }
    
    // Delete functionality
    let deleteId = null;
    let deleteFilename = null;
    const confirmModal = document.getElementById('confirmModal');
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    const cancelDeleteBtn = document.getElementById('cancelDelete');
    
    window.deleteImage = function(id, filename) {
        deleteId = id;
        deleteFilename = filename;
        confirmModal.style.display = 'flex';
    };
    
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            if (deleteId && deleteFilename) {
                // Send delete request
                const xhr = new XMLHttpRequest();
                const formData = new FormData();
                formData.append('id', deleteId);
                formData.append('filename', deleteFilename);
                
                xhr.open('POST', 'delete.php');
                xhr.send(formData);
                
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            showNotification('Image deleted successfully!', 'success');
                            
                            // Remove the image element from the DOM
                            const imageElement = document.querySelector(`[onclick="deleteImage(${deleteId}, '${deleteFilename}')"]`);
                            if (imageElement) {
                                const galleryItem = imageElement.closest('.gallery-item');
                                if (galleryItem) {
                                    galleryItem.style.opacity = '0';
                                    galleryItem.style.transform = 'scale(0.8)';
                                    
                                    setTimeout(() => {
                                        galleryItem.remove();
                                        
                                        // If no images left, show empty state
                                        const galleryGrid = document.getElementById('galleryGrid');
                                        if (galleryGrid && galleryGrid.children.length === 0) {
                                            window.location.reload();
                                        }
                                    }, 300);
                                }
                            }
                        } else {
                            showNotification(response.message, 'error');
                        }
                    } else {
                        showNotification('Delete failed. Please try again.', 'error');
                    }
                    
                    confirmModal.style.display = 'none';
                    deleteId = null;
                    deleteFilename = null;
                };
            }
        });
    }
    
    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', function() {
            confirmModal.style.display = 'none';
            deleteId = null;
            deleteFilename = null;
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === confirmModal) {
            confirmModal.style.display = 'none';
            deleteId = null;
            deleteFilename = null;
        }
    });
    
    // Increment views
    window.incrementViews = function(imageId, buttonElement = null) {
        const formData = new FormData();
        formData.append('id', imageId);

        fetch('view.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    return;
                }

                const totalViewsCount = document.getElementById('totalViewsCount');
                if (totalViewsCount && typeof data.total_views !== 'undefined') {
                    totalViewsCount.textContent = data.total_views;
                }

                const targetButtons = buttonElement
                    ? [buttonElement]
                    : document.querySelectorAll(`.view-btn[data-image-id="${imageId}"]`);

                targetButtons.forEach(button => {
                    button.innerHTML = `<i class="fas fa-eye"></i> ${data.views}`;
                });
            })
            .catch(() => {
                // Avoid interrupting the image open flow if view tracking fails.
            });
    };

    document.querySelectorAll('.gallery-link[data-image-id]').forEach(link => {
        link.addEventListener('click', function() {
            const imageId = this.dataset.imageId;
            if (imageId) {
                window.incrementViews(imageId);
            }
        });
    });
    
    // Notification function
    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        
        // Style the notification
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#4caf50' : '#f72585'};
            color: Yellow;
            padding: 15px 25px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1001;
            animation: slideIn 0.3s ease, fadeOut 0.3s ease 2.7s;
        `;
        
        // Add keyframes for animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
        `;
        document.head.appendChild(style);
        
        // Add to page
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }
    
    // Lightbox configuration
    if (typeof lightbox !== 'undefined') {
        lightbox.option({
            'resizeDuration': 300,
            'wrapAround': true,
            'albumLabel': 'Image %1 of %2',
            'fadeDuration': 300,
            'imageFadeDuration': 300
        });
    }
});
