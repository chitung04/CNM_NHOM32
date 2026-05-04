<?php
session_start();

// Set up test session
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['full_name'] = 'Test Admin';
$_SESSION['role'] = 'manager';

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Banner Position</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Debug styles */
        .debug-navbar {
            border: 3px solid red !important;
        }
        
        .debug-sidebar {
            border: 3px solid blue !important;
        }
        
        .debug-banner {
            border: 3px solid green !important;
        }
        
        .debug-content {
            border: 3px solid purple !important;
        }
    </style>
</head>
<body>
    <!-- Navbar với debug border -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top debug-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">🔴 NAVBAR (56px)</a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item d-flex align-items-center">
                    <div id="notification-text" class="notification-text-inline" style="display: none;">
                        <div class="notification-message">Test notification</div>
                    </div>
                </div>
                
                <div class="nav-item dropdown position-relative">
                    <a class="nav-link position-relative" href="#" role="button">
                        <i class="bi bi-bell fs-5" id="notification-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-count" style="display: none;">0</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Notification Banner với debug border -->
    <div id="notification-banner" class="notification-banner debug-banner">
        <div id="notification-banner-content" class="notification-banner-content">
            <div class="notification-item">
                <span class="icon">⚠️</span>
                <span class="message">🟢 BANNER - Dưới navbar và không đè lên sidebar</span>
            </div>
            <div class="notification-item">
                <span class="icon">📋</span>
                <span class="message">Thông báo test để kiểm tra vị trí</span>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar với debug border -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar debug-sidebar">
                <div class="position-sticky pt-3">
                    <h6 class="text-primary">🔵 SIDEBAR</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-cart-plus me-2"></i>
                                Bán hàng
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-receipt me-2"></i>
                                Lịch sử đơn hàng
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-capsule me-2"></i>
                                Tra cứu thuốc
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-bell me-2"></i>
                                Thông báo
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Main Content với debug border -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content debug-content">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5>🟣 MAIN CONTENT - Test Vị Trí Banner</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <h6>📍 Vị trí CHÍNH XÁC:</h6>
                            <ul>
                                <li><strong>🔴 Navbar:</strong> Trên cùng - 56px</li>
                                <li><strong>🔵 Sidebar:</strong> Bên trái, từ navbar xuống cuối</li>
                                <li><strong>🟢 Banner:</strong> Cuối màn hình, BÊN PHẢI sidebar (không vào menu)</li>
                                <li><strong>🟣 Content:</strong> Bên phải sidebar, có padding-bottom</li>
                            </ul>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>✅ HOÀN HẢO:</h6>
                                <ul>
                                    <li>Banner KHÔNG vào trong menu ✓</li>
                                    <li>Banner chạy BÊN PHẢI sidebar ✓</li>
                                    <li>Banner tới mép menu ✓</li>
                                    <li>Menu không bị đè ✓</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>🎯 Vị trí cuối cùng:</h6>
                                <p><strong>Desktop:</strong> Cuối màn hình, bên phải sidebar</p>
                                <p><strong>Mobile:</strong> Cuối màn hình, full width</p>
                                <button class="btn btn-success" onclick="showInfo()">
                                    ✅ Kiểm tra vị trí cuối
                                </button>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <div id="size-info" class="bg-light p-3 rounded">
                                <em>Nhấn nút trên để xem thông tin...</em>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
    function showInfo() {
        const banner = $('#notification-banner');
        const navbar = $('.navbar');
        const sidebar = $('.sidebar');
        const content = $('.main-content');
        
        const info = `
            <div class="row">
                <div class="col-md-6">
                    <h6>📏 Kích thước mới:</h6>
                    <p><strong>Navbar height:</strong> ${navbar.height()}px</p>
                    <p><strong>Banner height:</strong> ${banner.height()}px</p>
                    <p><strong>Banner position:</strong> ${banner.css('position')}</p>
                    <p><strong>Banner bottom:</strong> ${banner.css('bottom')}</p>
                    <p><strong>Banner left:</strong> ${banner.css('left')}</p>
                    <p><strong>Banner width:</strong> ${banner.css('width')}</p>
                </div>
                <div class="col-md-6">
                    <h6>📱 Responsive:</h6>
                    <p><strong>Screen width:</strong> ${$(window).width()}px</p>
                    <p><strong>Content margin-top:</strong> ${content.css('margin-top')}</p>
                    <p><strong>Banner visible:</strong> ${banner.is(':visible') ? '✅' : '❌'}</p>
                    <p><strong>Animation running:</strong> ${$('#notification-banner-content').css('animation-name') !== 'none' ? '✅' : '❌'}</p>
                </div>
            </div>
        `;
        
        $('#size-info').html(info);
    }
    
    $(document).ready(function() {
        console.log('Banner position test ready');
        
        // Khởi tạo banner
        updateNotificationBanner([]);
        
        // Auto show info sau 2 giây
        setTimeout(showInfo, 2000);
    });
    </script>
</body>
</html>