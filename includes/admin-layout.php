<?php
/**
 * Admin Layout Wrapper
 * Provides consistent layout structure for admin pages
 */
$pageTitle = $pageTitle ?? 'Admin';
$showHeader = $showHeader ?? true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($pageTitle); ?> - Restaurant Menu Platform</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo htmlspecialchars(defined('SITE_URL') ? SITE_URL . '/assets/css/admin.css' : '../assets/css/admin.css'); ?>">

<style>
:root{
  --bg:#f2f4f7;
  --sidebar:#0f172a;
  --primary:#D97706;
  --primary-dark:#B91C1C;
  --danger:#ef4444;
  --text:#111827;
  --muted:#6b7280;
  --card:#ffffff;
  --radius:14px;
}

*{
  box-sizing:border-box;
  margin:0;
  padding:0;
}

html,body{
  overflow-x:hidden;
}

body{
  font-family:Inter,sans-serif;
  background:var(--bg);
  color:var(--text);
}

/* ===== APP LAYOUT ===== */
.app{
  display:flex;
  min-height:100vh;
}

/* ===== MOBILE HAMBURGER ===== */
.mobile-hamburger{
  position:fixed;
  top:12px;
  left:12px;
  z-index:25;
  padding:8px;
  border-radius:8px;
  background:white;
  box-shadow:0 2px 4px rgba(0,0,0,0.1);
  border:1px solid #e2e8f0;
  display:none;
  align-items:center;
  justify-content:center;
  cursor:pointer;
  transition:all 0.2s;
}

.mobile-hamburger.hidden{
  display:none !important;
}

.mobile-hamburger:hover{
  background:#f8fafc;
}

.mobile-hamburger .hamburger-icon{
  width:20px;
  height:20px;
  color:#475569;
}

/* ===== SIDEBAR OVERLAY ===== */
.sidebar-overlay{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,0.4);
  backdrop-filter:blur(4px);
  z-index:30;
  display:none;
  transition:opacity 0.3s;
}

.sidebar-overlay.show{
  display:block;
}

/* ===== MODERN SIDEBAR ===== */
.sidebar-modern{
  position:fixed;
  top:0;
  left:0;
  height:100%;
  background:white;
  border-right:1px solid #e2e8f0;
  z-index:40;
  transition:all 0.3s ease-in-out;
  display:flex;
  flex-direction:column;
  width:312px;
  transform:translateX(-100%);
}

.sidebar-modern.open{
  transform:translateX(0);
}

.sidebar-modern.collapsed{
  width:112px;
}

/* ===== SIDEBAR HEADER ===== */
.sidebar-header-modern{
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:20px;
  border-bottom:1px solid #e2e8f0;
  background:rgba(248,250,252,0.6);
}

.sidebar-logo-wrapper{
  flex:1;
}

.sidebar-logo{
  display:flex;
  align-items:center;
  gap:10px;
}

.logo-icon-modern{
  width:36px;
  height:36px;
  background:var(--primary);
  border-radius:8px;
  display:flex;
  align-items:center;
  justify-content:center;
  color:white;
  font-weight:700;
  font-size:16px;
  box-shadow:0 1px 2px 0 rgba(0,0,0,0.05);
}

.logo-icon-modern-centered{
  width:36px;
  height:36px;
  background:var(--primary);
  border-radius:8px;
  display:flex;
  align-items:center;
  justify-content:center;
  color:white;
  font-weight:700;
  font-size:16px;
  margin:0 auto;
  box-shadow:0 1px 2px 0 rgba(0,0,0,0.05);
}

.logo-text{
  display:flex;
  flex-direction:column;
}

.logo-title{
  font-weight:600;
  color:#1e293b;
  font-size:16px;
}

.logo-subtitle{
  font-size:12px;
  color:#64748b;
}

/* Hide logo text when collapsed */
.sidebar-modern.collapsed .logo-text{
  display:none;
}

.sidebar-modern.collapsed .sidebar-logo{
  justify-content:center;
}

.collapse-btn-modern{
  display:none;
  padding:6px;
  border-radius:6px;
  border:none;
  background:none;
  cursor:pointer;
  transition:all 0.2s;
}

.collapse-btn-modern:hover{
  background:#f1f5f9;
}

.collapse-icon{
  width:16px;
  height:16px;
  color:#64748b;
}

/* ===== SEARCH ===== */
.sidebar-search{
  padding:12px 16px;
}

.search-wrapper{
  position:relative;
}

.search-icon{
  position:absolute;
  left:12px;
  top:50%;
  transform:translateY(-50%);
  width:14px;
  height:14px;
  color:#94a3b8;
}

.search-input{
  width:100%;
  padding:8px 16px 8px 36px;
  background:#f8fafc;
  border:1px solid #e2e8f0;
  border-radius:6px;
  font-size:14px;
  color:#1e293b;
  transition:all 0.2s;
}

.search-input::placeholder{
  color:#94a3b8;
}

.search-input:focus{
  outline:none;
  border-color:var(--primary);
  box-shadow:0 0 0 2px rgba(217,119,6,0.15);
}

/* ===== NAVIGATION ===== */
.sidebar-nav{
  flex:1;
  padding:8px 12px;
  overflow-y:auto;
}

.nav-list{
  list-style:none;
  display:flex;
  flex-direction:column;
  gap:2px;
}

.nav-item{
  width:100%;
  display:flex;
  align-items:center;
  gap:10px;
  padding:10px 12px;
  border-radius:6px;
  text-decoration:none;
  transition:all 0.2s;
  position:relative;
  color:#475569;
}

.nav-item:hover{
  background:#f8fafc;
  color:#0f172a;
}

.nav-item.active{
  background:rgba(217,119,6,0.1);
  color:#92400E;
}

.nav-icon-wrapper{
  display:flex;
  align-items:center;
  justify-content:center;
  min-width:24px;
}

.nav-icon{
  width:18px;
  height:18px;
  flex-shrink:0;
}

.nav-item.active .nav-icon{
  color:var(--primary);
}

.nav-item:not(.active) .nav-icon{
  color:#64748b;
}

.nav-item:hover:not(.active) .nav-icon{
  color:#334155;
}

.nav-text{
  font-size:14px;
  font-weight:400;
}

.nav-item.active .nav-text{
  font-weight:500;
}

.sidebar-modern.collapsed .nav-text{
  display:none;
}

.sidebar-modern.collapsed .nav-item{
  justify-content:center;
  padding:10px 8px;
}

/* ===== SIDEBAR FOOTER ===== */
.sidebar-footer-modern{
  margin-top:auto;
  border-top:1px solid #e2e8f0;
}

.sidebar-profile{
  border-bottom:1px solid #e2e8f0;
  background:rgba(248,250,252,0.3);
  padding:12px;
}

.profile-card{
  display:flex;
  align-items:center;
  padding:8px 12px;
  border-radius:6px;
  background:white;
  transition:background 0.2s;
}

.profile-card:hover{
  background:#f8fafc;
}

.profile-avatar{
  width:32px;
  height:32px;
  background:#e2e8f0;
  border-radius:50%;
  display:flex;
  align-items:center;
  justify-content:center;
  color:#475569;
  font-weight:500;
  font-size:14px;
}

.profile-info{
  flex:1;
  min-width:0;
  margin-left:10px;
}

.profile-name{
  font-size:14px;
  font-weight:500;
  color:#1e293b;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}

.profile-role{
  font-size:12px;
  color:#64748b;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}

.profile-status{
  width:8px;
  height:8px;
  background:#22c55e;
  border-radius:50%;
  margin-left:8px;
}

.profile-avatar-centered{
  display:flex;
  justify-content:center;
  position:relative;
}

.profile-avatar-small{
  width:36px;
  height:36px;
  background:#e2e8f0;
  border-radius:50%;
  display:flex;
  align-items:center;
  justify-content:center;
  color:#475569;
  font-weight:500;
  font-size:14px;
}

.profile-status-small{
  position:absolute;
  bottom:-4px;
  right:-4px;
  width:12px;
  height:12px;
  background:#22c55e;
  border-radius:50%;
  border:2px solid white;
}

.sidebar-logout{
  padding:12px;
}

.logout-btn{
  width:100%;
  display:flex;
  align-items:center;
  gap:10px;
  padding:10px 12px;
  border-radius:6px;
  text-decoration:none;
  transition:all 0.2s;
  color:#dc2626;
}

.logout-btn .nav-icon-wrapper{
  display:flex;
  align-items:center;
  justify-content:center;
  min-width:24px;
  height:24px;
}

.logout-btn .nav-text{
  line-height:24px;
  display:flex;
  align-items:center;
}

.logout-btn:hover{
  background:#fef2f2;
  color:#b91c1c;
}

.logout-icon{
  color:#ef4444;
}

.logout-btn:hover .logout-icon{
  color:#dc2626;
}

.sidebar-modern.collapsed .logout-btn{
  justify-content:center;
  padding:10px 8px;
}

.sidebar-modern.collapsed .logout-btn .nav-text{
  display:none;
}

/* ===== CONTENT ===== */
.content{
  margin-left:0;
  flex:1;
  transition:all 0.3s ease-in-out;
  width:100%;
}

.sidebar-modern.open ~ .content,
.sidebar-modern:not(.collapsed) ~ .content{
  margin-left:312px;
}

.sidebar-modern.collapsed ~ .content{
  margin-left:112px;
}

/* ===== HEADER ===== */
.header{
  background:#fff;
  padding:14px 20px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  box-shadow:0 1px 4px rgba(0,0,0,.06);
  position:sticky;
  top:0;
  z-index:20;
}

.mobile-toggle{
  display:none;
  font-size:1.4rem;
  background:none;
  border:none;
  cursor:pointer;
  color:var(--text);
}

.header-title{
  font-weight:600;
  font-size:1.1rem;
}

.restaurant-name{
  color:var(--primary);
  font-weight:600;
}

/* ===== MAIN ===== */
main{
  padding:24px;
}

/* ===== CARDS ===== */
.card{
  background:#fff;
  border-radius:var(--radius);
  box-shadow:0 12px 30px rgba(0,0,0,.06);
  padding:24px;
  margin-bottom:24px;
}

.card-header{
  margin-bottom:20px;
  padding-bottom:16px;
  border-bottom:1px solid #e5e7eb;
}

.card-title{
  font-size:1.1rem;
  font-weight:600;
  color:var(--text);
}

/* ===== FORMS ===== */
.form-group{
  margin-bottom:20px;
}

.form-label{
  display:block;
  margin-bottom:8px;
  font-weight:500;
  color:var(--text);
  font-size:.9rem;
}

.form-input,
.form-textarea,
.form-select{
  width:100%;
  padding:10px 14px;
  border:2px solid #e5e7eb;
  border-radius:10px;
  font-size:.9rem;
  font-family:Inter,sans-serif;
  transition:.2s;
}

.form-input:focus,
.form-textarea:focus,
.form-select:focus{
  outline:none;
  border-color:var(--primary);
  box-shadow:0 0 0 3px rgba(79,70,229,.1);
}

.form-textarea{
  resize:vertical;
  min-height:100px;
}

/* ===== BUTTONS ===== */
.btn{
  padding:10px 20px;
  border-radius:10px;
  border:none;
  font-size:.9rem;
  font-weight:500;
  cursor:pointer;
  text-decoration:none;
  display:inline-flex;
  align-items:center;
  gap:8px;
  transition:.2s;
}

.btn-primary{
  background:var(--primary);
  color:#fff;
}

.btn-primary:hover{
  background:var(--primary-dark);
  transform:translateY(-1px);
}

.btn-secondary{
  background:#e5e7eb;
  color:var(--text);
}

.btn-secondary:hover{
  background:#d1d5db;
}

.btn-danger{
  background:var(--danger);
  color:#fff;
}

.btn-danger:hover{
  background:#dc2626;
}

.btn-small{
  padding:7px 14px;
  font-size:.8rem;
}

/* ===== TABLES ===== */
.table{
  width:100%;
  border-collapse:collapse;
}

.table th,
.table td{
  padding:12px;
  text-align:left;
  border-bottom:1px solid #e5e7eb;
}

.table th{
  background:#f9fafb;
  font-weight:600;
  color:var(--muted);
  font-size:.85rem;
  text-transform:uppercase;
}

.table tr:hover{
  background:#f9fafb;
}

/* ===== MESSAGES ===== */
.message{
  padding:14px 16px;
  border-radius:10px;
  margin-bottom:20px;
  display:flex;
  align-items:center;
  gap:10px;
}

.message-success{
  background:#d1fae5;
  color:#065f46;
}

.message-error{
  background:#fee2e2;
  color:#991b1b;
}

/* ===== MOBILE ===== */
@media(max-width:768px){
  .mobile-hamburger{
    display:flex !important;
    position:fixed;
    top:12px;
    left:12px;
    z-index:25;
  }
  
  .mobile-hamburger.hidden{
    display:none !important;
  }
  
  .sidebar-modern{
    transform:translateX(-100%);
  }
  
  .sidebar-modern.mobile-open{
    transform:translateX(0);
  }
  
  .sidebar-overlay.show{
    display:block;
  }
  
  .content{
    margin-left:0 !important;
  }
  
  .collapse-btn-modern{
    display:none;
  }
  
  /* Header mobile adjustments */
  .header{
    position:fixed;
    top:0;
    left:0;
    right:0;
    padding:12px 16px;
    padding-left:52px; /* Space for hamburger */
    z-index:20;
    flex-wrap:wrap;
    gap:8px;
  }
  
  .header > div:last-child{ margin-left:auto; }
  
  .header-title{
    font-size:1rem;
  }
  
  /* Add padding to main so content isn't hidden under fixed header */
  main{
    padding-top:64px;
  }
  
  /* Card responsive */
  .card{
    border-radius:8px;
    margin:0 -8px;
  }
  
  .card-header{
    flex-direction:column;
    gap:12px;
    align-items:stretch !important;
    padding:16px;
  }
  
  .card-header .btn{
    width:100%;
    justify-content:center;
  }
  
  /* Table responsive - horizontal scroll */
  .table-responsive,
  .card{
    overflow-x:auto;
    -webkit-overflow-scrolling:touch;
  }
  
  .table{
    min-width:600px;
  }
  
  .table th,
  .table td{
    padding:10px 8px;
    font-size:0.85rem;
    white-space:nowrap;
  }
  
  .table td:last-child{
    white-space:nowrap;
  }
  
  /* Button groups */
  .btn-group,
  [style*="display: flex"][style*="gap"]{
    flex-wrap:wrap;
  }
  
  .btn-small{
    padding:6px 10px;
    font-size:0.75rem;
  }
  
  /* Form responsive */
  .form-group{
    margin-bottom:16px;
  }
  
  /* Modal responsive */
  .modal-content{
    width:95%;
    max-height:85vh;
    margin:10px;
  }
  
  .modal-header{
    padding:16px;
  }
  
  .modal-body{
    padding:16px;
  }
  
  .modal-footer{
    padding:16px;
    flex-direction:column;
    gap:8px;
  }
  
  .modal-footer .btn{
    width:100%;
  }
  
  /* Stats cards responsive */
  .stats-grid{
    grid-template-columns:1fr !important;
  }
  
  /* Flex layouts responsive */
  [style*="display: flex"][style*="gap: 15px"],
  [style*="display:flex"][style*="gap:15px"]{
    flex-direction:column;
  }
  
  [style*="display: grid"][style*="grid-template-columns"]{
    grid-template-columns:1fr !important;
  }
}

@media(min-width:769px){
  .sidebar-modern{
    transform:translateX(0) !important;
  }
  
  .sidebar-modern.open{
    transform:translateX(0) !important;
  }
  
  .collapse-btn-modern{
    display:flex;
  }
  
  .content{
    margin-left:312px !important;
  }
  
  .sidebar-modern.collapsed ~ .content{
    margin-left:112px !important;
  }
  
  .sidebar-overlay{
    display:none !important;
  }
  
  .mobile-hamburger{
    display:none !important;
  }
}
</style>
</head>

<body>

<div class="app">
  <?php include __DIR__ . '/sidebar-admin.php'; ?>

  <div class="content">
    <?php if ($showHeader): ?>
    <div class="header">
      <div>
        <?php if (isset($showBackToDashboard) && $showBackToDashboard): ?>
          <a href="/admin/dashboard.php" class="btn btn-secondary btn-small" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Dashboard
          </a>
        <?php endif; ?>
      </div>
      <div class="header-title"><?php echo htmlspecialchars($pageTitle); ?></div>
      <div></div>
    </div>
    <?php endif; ?>

    <main>
      <?php if (isset($message) && $message): ?>
        <div class="message message-success"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>
      
      <?php if (isset($error) && $error): ?>
        <div class="message message-error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

