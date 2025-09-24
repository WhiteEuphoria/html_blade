<div
    <?php echo e($attributes
            ->merge([
                'id' => $getId(),
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)); ?>

>
    <?php echo e($getChildComponentContainer()); ?>

</div>
<?php /**PATH /Users/admin/Desktop/html_blade-codex-move-personal-acc-assets-to-resources-or-public/vendor/filament/forms/resources/views/components/group.blade.php ENDPATH**/ ?>