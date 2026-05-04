            </main>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <!-- Initialize notifications -->
    <script>
    $(document).ready(function() {
        console.log('Footer script initializing...');
        
        // LUÔN HIỂN THỊ BANNER NGAY LẬP TỨC
        console.log('Initializing banner immediately...');
        
        // Khởi tạo banner với thông báo mặc định
        updateNotificationBanner([]);
        
        // Load notifications thật từ server
        loadNotifications();
        
        // Đảm bảo banner luôn hiển thị
        setTimeout(function() {
            const banner = $('#notification-banner');
            const bannerContent = $('#notification-banner-content');
            
            if (bannerContent.html().length === 0) {
                console.log('Banner content empty, forcing default content...');
                updateNotificationBanner([]);
            }
            
            console.log('Banner status check:');
            console.log('- Banner visible:', banner.is(':visible'));
            console.log('- Banner height:', banner.height());
            console.log('- Content length:', bannerContent.html().length);
        }, 2000);
    });
    </script>
</body>
</html>
