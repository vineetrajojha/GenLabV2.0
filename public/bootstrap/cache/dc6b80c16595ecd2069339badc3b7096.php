<?php
    $pageTitle = 'Marketing Dashboard';
    $metricLookup = collect($payload['metrics'] ?? [])->pluck('value', 'label');
    $insightMessage = $payload['insights']['message'] ?? 'Keep campaigns aligned with approved budgets and booking targets.';
?>

<?php $__env->startSection('title', $pageTitle); ?>

<?php $__env->startSection('content'); ?>
    
    <?php
        // `marketingPerson` is the current marketing user by default; controllers may pass a different model
        // Prefer the web guard (marketing user), fall back to payload or generic auth user
        $marketingPerson = $payload['marketingPerson'] ?? (Auth::guard('web')->check() ? Auth::guard('web')->user() : (Auth::check() ? Auth::user() : ($user ?? null)));
        $stats = $payload['stats'] ?? [];
    ?>

    <?php echo $__env->make('superadmin.accounts.marketingPerson.profile', ['marketingPerson' => $marketingPerson, 'stats' => $stats], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Mamp\htdocs\GenLabV2.0\resources\views/superadmin/departments/marketing/dashboard.blade.php ENDPATH**/ ?>