<?php
/** @var \Kirby\Cms\Block $block */

use JamieHumm\Microlighter\Assets;

$code = $block->code()->value();

if (trim((string)$code) === '') {
    return;
}

$language = $block->language()->or('javascript')->value();
$theme    = Assets::theme();
$caption  = $block->filename()->value();

// Only output assets on pages that have a code block, and only once
echo Assets::once();
?>
<?php /* Theme attribute on the figure so the caption can use the --syntax-* colours too */ ?>
<figure class="block-microcode" data-syntax-theme="<?= esc($theme, 'attr') ?>">
  <?php if ($caption): ?>
  <figcaption><?= esc($caption) ?></figcaption>
  <?php endif ?>
  <micro-lighter
    <?= $block->lineNumbers()->toBool() ? 'line-numbers' : '' ?>
    <?php /* Defaults to on, so blocks saved before this toggle existed get the button too */ ?>
    <?= $block->copyButton()->toBool(true) ? 'controls="copy"' : '' ?>
  >
    <?php /* No whitespace between <pre><code> and the code, or it renders as extra blank lines */ ?>
    <pre><code class="language-<?= esc($language, 'attr') ?>"><?= esc($code) ?></code></pre>
  </micro-lighter>
</figure>
