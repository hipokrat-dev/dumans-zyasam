<?php if($s['audio']): ?>
<audio id="ambience" src="<?=e($s['audio'])?>" loop preload="auto"></audio>
<button id="audioToggle" class="sound-toggle" type="button" aria-pressed="false" aria-label="Sesi aç" title="Sesi aç">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 5 6 9H3v6h3l5 4V5Z"/><g class="sound-waves"><path d="M15 8a6 6 0 0 1 0 8M18 5a10 10 0 0 1 0 14"/></g><path class="sound-off" d="m16 9 5 6m0-6-5 6"/></svg>
</button><span id="audioStatus" class="sr-only" role="status"></span>
<?php endif; ?>
