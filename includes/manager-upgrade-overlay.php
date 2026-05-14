<?php
/**
 * Manager upgrade overlay: shown on manager pages when plan doesn't include the feature.
 * Expects: $managerUpgradeFeature ('food_ordering'|'table_reservations'|'payments'), $managerUpgradePlans (array), $managerUpgradeBillingUrl (string).
 */
if (empty($managerUpgradeFeature)) {
    return;
}

$title = 'Feature';
$message = 'Upgrade your plan to unlock this feature.';
if ($managerUpgradeFeature === 'food_ordering') {
    $title = 'Food ordering';
    $message = 'Upgrade your plan to accept orders and manage them here.';
} elseif ($managerUpgradeFeature === 'table_reservations') {
    $title = 'Table reservations';
    $message = 'Upgrade your plan to enable table reservations and manage them here.';
} elseif ($managerUpgradeFeature === 'payments') {
    $title = 'Payments';
    $message = 'Upgrade your plan to enable online payments for orders and reservations.';
}
$billingUrl = !empty($managerUpgradeBillingUrl) ? $managerUpgradeBillingUrl : '/manager/billing.php';
?>
<style>
.resmenu-manager-upgrade-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: rgba(15, 23, 42, 0.4);
    backdrop-filter: blur(2px);
}
.resmenu-manager-upgrade-overlay .resmenu-manager-upgrade-card {
    background: var(--card, #fff);
    border-radius: var(--radius, 14px);
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    max-width: 420px;
    width: 100%;
    padding: 28px 24px;
    text-align: center;
}
.resmenu-manager-upgrade-overlay .resmenu-manager-upgrade-card h2 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text, #111827);
    margin-bottom: 8px;
}
.resmenu-manager-upgrade-overlay .resmenu-manager-upgrade-card p {
    color: var(--muted, #6b7280);
    font-size: 0.9375rem;
    margin-bottom: 20px;
    line-height: 1.5;
}
.resmenu-manager-upgrade-overlay .resmenu-manager-upgrade-plans {
    list-style: none;
    margin: 0 0 24px 0;
    padding: 0;
    text-align: left;
}
.resmenu-manager-upgrade-overlay .resmenu-manager-upgrade-plans li {
    padding: 10px 12px;
    background: var(--bg, #f2f4f7);
    border-radius: 8px;
    margin-bottom: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text, #111827);
}
.resmenu-manager-upgrade-overlay .resmenu-manager-upgrade-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.resmenu-manager-upgrade-overlay .resmenu-manager-upgrade-actions a,
.resmenu-manager-upgrade-overlay .resmenu-manager-upgrade-actions button {
    display: inline-block;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9375rem;
    text-align: center;
    text-decoration: none;
    border: none;
    cursor: pointer;
}
.resmenu-manager-upgrade-overlay .resmenu-manager-upgrade-actions .btn-upgrade {
    background: var(--primary, #1e3a5f);
    color: #fff;
}
.resmenu-manager-upgrade-overlay .resmenu-manager-upgrade-actions .btn-upgrade:hover {
    opacity: 0.9;
}
.resmenu-manager-upgrade-overlay .resmenu-manager-upgrade-actions .btn-close {
    background: transparent;
    color: var(--muted, #6b7280);
}
.resmenu-manager-upgrade-overlay .resmenu-manager-upgrade-actions .btn-close:hover {
    background: var(--bg, #f2f4f7);
}
.resmenu-manager-blurred {
    filter: blur(4px);
    pointer-events: none;
    user-select: none;
}
</style>
<div class="resmenu-manager-upgrade-overlay" id="resmenu-manager-upgrade-overlay">
    <div class="resmenu-manager-upgrade-card">
        <h2>Upgrade for <?php echo htmlspecialchars($title); ?></h2>
        <p><?php echo htmlspecialchars($message); ?></p>
        <?php if (!empty($managerUpgradePlans)): ?>
        <ul class="resmenu-manager-upgrade-plans">
            <?php foreach ($managerUpgradePlans as $p): ?>
            <li><?php echo htmlspecialchars($p['name'] ?? $p['slug'] ?? 'Plan'); ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <div class="resmenu-manager-upgrade-actions">
            <a href="<?php echo htmlspecialchars($billingUrl); ?>" class="btn-upgrade">Upgrade plan</a>
            <button type="button" class="btn-close" onclick="document.getElementById('resmenu-manager-upgrade-overlay').style.display='none';">Close</button>
        </div>
    </div>
</div>
