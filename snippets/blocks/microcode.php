<?php
/** @var \Kirby\Cms\Block $block */

use JamieHumm\Microlighter\Assets;

$code = $block->code()->value();

if (trim((string)$code) === '') {
    return;
}

$language = $block->language()->or('javascript')->value();
$theme    = Assets::theme();
$mode     = Assets::mode();
$caption  = $block->filename()->value();

// Only output assets on pages that have a code block, and only once
echo Assets::once();
?>
<?php /* Theme attribute on the figure so the caption can use the --syntax-* colours too */ ?>
<?php /* Themes use light-dark(), so pinning color-scheme picks one side of every colour */ ?>
<figure class="block-microcode" data-syntax-theme="<?= esc($theme, 'attr') ?>"<?= $mode !== 'auto' ? ' style="color-scheme: ' . $mode . '"' : '' ?>>
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
