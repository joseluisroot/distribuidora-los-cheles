<?php
// $pager (CodeIgniter\Pager\PagerRenderer)
// Se espera $query con el resto del querystring para preservarlo
$query = $query ?? [];
$qs = function(array $extra = []) use ($query) {
    $merged = array_merge($query, $extra);
    return $merged ? ('?' . http_build_query($merged)) : '';
};
?>

<?php if ($pager->hasPrevious()) : ?>
    <a class="px-3 py-2 border rounded-l-lg hover:bg-slate-100"
       href="<?= $pager->getPreviousPage() . $qs(['page_catalogo' => $pager->getPreviousPageNumber()]) ?>">
        «
    </a>
<?php else: ?>
    <span class="px-3 py-2 border rounded-l-lg opacity-50 cursor-not-allowed">«</span>
<?php endif; ?>

<?php foreach ($pager->links() as $link): ?>
    <?php if ($link['active']): ?>
        <span class="px-3 py-2 border-t border-b font-semibold bg-slate-100"><?= $link['title'] ?></span>
    <?php else: ?>
        <a class="px-3 py-2 border-t border-b hover:bg-slate-100"
           href="<?= $link['uri'] . $qs(['page_catalogo' => $link['title']]) ?>">
            <?= $link['title'] ?>
        </a>
    <?php endif; ?>
<?php endforeach; ?>

<?php if ($pager->hasNext()) : ?>
    <a class="px-3 py-2 border rounded-r-lg hover:bg-slate-100"
       href="<?= $pager->getNextPage() . $qs(['page_catalogo' => $pager->getNextPageNumber()]) ?>">
        »
    </a>
<?php else: ?>
    <span class="px-3 py-2 border rounded-r-lg opacity-50 cursor-not-allowed">»</span>
<?php endif; ?>
