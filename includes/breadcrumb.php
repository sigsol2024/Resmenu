<?php
/**
 * Breadcrumb Component
 * Usage: Set $breadcrumbs array before including this file
 * Example: $breadcrumbs = [['label' => 'Home', 'url' => '/'], ['label' => 'Current Page']];
 */
if (!isset($breadcrumbs)) {
    $breadcrumbs = [];
}
?>
<nav class="breadcrumb-nav" aria-label="Breadcrumb">
    <div class="breadcrumb-container">
        <ol class="breadcrumb-list">
            <?php foreach ($breadcrumbs as $index => $crumb): ?>
                <li class="breadcrumb-item">
                    <?php if (isset($crumb['url']) && $index < count($breadcrumbs) - 1): ?>
                        <a href="<?php echo htmlspecialchars($crumb['url']); ?>" class="breadcrumb-link">
                            <?php echo htmlspecialchars($crumb['label']); ?>
                        </a>
                        <span class="breadcrumb-separator">/</span>
                    <?php else: ?>
                        <span class="breadcrumb-current" aria-current="page">
                            <?php echo htmlspecialchars($crumb['label']); ?>
                        </span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>

