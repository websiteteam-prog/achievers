<?php
declare(strict_types=1);

$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$q = $q ?? [];
$questionImage = trim((string)($q['question_image'] ?? ''));
$payloadRaw = (string)($q['question_payload'] ?? '[]');

$makeUrl = function (string $path) use ($h): string {
    if ($path === '') return '';
    if (preg_match('~^https?://~i', $path)) return $path;
    $root = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
    $root = $root === '' ? '/' : $root;
    return $root . '/' . ltrim($path, '/');
};

$qid = (int)($q['id'] ?? 0);

$decoded = json_decode($payloadRaw, true);
if (!is_array($decoded)) $decoded = [];

/* ---- NEW: grouped "sections" format detect (1 question me 3 section) ---- */
$sections = null;
$comboImage = $questionImage;
if (isset($decoded['sections']) && is_array($decoded['sections'])) {
    $sections = $decoded['sections'];
    if (!empty($decoded['image'])) $comboImage = $decoded['image'];
    // flatten (backcompat detections + grid max ke liye)
    $items = [];
    foreach ($sections as $sec) {
        $stype = $sec['type'] ?? 'coord_to_name';
        foreach (($sec['items'] ?? []) as $it) {
            if (!isset($it['type'])) $it['type'] = $stype;
            $items[] = $it;
        }
    }
} else {
    $items = $decoded; // purana flat format
}

$imgUrl = $makeUrl($sections !== null ? $comboImage : $questionImage);

// coord_list (single-mode canvas) detect
$isQue3 = false;
foreach ($items as $item) {
    if (($item['type'] ?? '') === 'coord_list') { $isQue3 = true; break; }
}
// plot_connect (single-mode connect-the-dots) detect
$isPlotConnect = false;
foreach ($items as $item) {
    if (($item['type'] ?? '') === 'plot_connect') { $isPlotConnect = true; break; }
}

// grid max (10 se bada + override)
$gridMax = 10;
$gridMaxOverride = null;
if (isset($decoded['grid_max'])) $gridMaxOverride = (int)$decoded['grid_max'];
foreach ($items as $item) {
    if (isset($item['grid_max'])) $gridMaxOverride = (int)$item['grid_max'];
    if (preg_match_all('/\d+/', (string)($item['coord'] ?? ''), $mm)) {
        foreach ($mm[0] as $n) { $gridMax = max($gridMax, (int)$n); }
    }
}
if ($gridMaxOverride !== null) $gridMax = $gridMaxOverride;
$gridMin = isset($decoded['grid_min']) ? (int)$decoded['grid_min'] : 0;
?>
<style>
.coord-wrap { margin:20px 0; padding:0 10px; font-family:'Segoe UI',sans-serif; max-width:none; }
.coord-title { font-weight:600; color:#222; margin:0 0 16px; text-align:center; font-size:18px; }
.coord-img {
    width: 100%;
    max-width: 100%;
    height: auto;
    max-height: 70vh;
    display: block;
    margin: 0 0 30px 0;
    border-radius: 12px;
    object-fit: contain;
    object-position: left;
}
.answers-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:18px 30px; max-width:800px; margin:0; }
.answer-row { display:flex; align-items:center; gap:12px; font-size:17px; }
.coord-text { font-family:monospace; color:#1976d2; }
.answer-label { font-weight:600; min-width:28px; color:#222; }
.answer-input {
    flex:1; border:none; border-bottom:3px solid #2b6cb0; padding:6px 0; font-size:17px;
    background:transparent; outline:none; min-width:150px; text-align:center; font-family:monospace;
}
.answer-input:focus { border-bottom-color:#1a73e8; }

/* grouped sections (1 question = 3 section) */
.cp-section { margin:22px 0; }
.cp-heading { color:#934b3f; font-weight:700; font-style:italic; font-size:16px; margin:0 0 12px; }

/* coord_list single-mode */
.coord-wrap.que3 .answers-grid { display:flex; flex-direction:column; gap:8px; }
.coord-wrap.que3 .answer-row { display:flex; align-items:center; flex-wrap:nowrap; gap:12px; }
.coord-wrap.que3 .answer-input { opacity:0; height:0; padding:0; margin:0; pointer-events:none; }

/* Canvas */
.canvas-container {
    position:relative; width:100%; max-width:800px; margin:14px 0; border:1px solid #ddd;
    border-radius:12px; overflow:hidden; box-shadow:0 6px 20px rgba(0,0,0,.12); background:#fff;
}
#drawCanvas { width:100%; height:500px; display:block; }
.clear-btn {
    position:absolute; top:12px; right:12px; padding:6px 12px; background:#e53e3e; color:#fff;
    border:none; border-radius:6px; font-size:14px; cursor:pointer; box-shadow:0 2px 6px rgba(0,0,0,.15);
}
.clear-btn:hover { background:#c53030; }

/* PLOT (connect / plot points) */
.pc-instruction{ color:#934b3f; font-weight:600; margin:8px 0 12px; }
.pc-coordlist{ display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px; }
.pc-chip{ font-family:monospace; font-size:14px; border:1px solid #ccd; border-radius:6px; padding:3px 8px; background:#f6f8fc; }
.pc-chip.active{ border-color:#1976d2; background:#e3f0ff; font-weight:700; }
.pc-chip.done{ opacity:.45; text-decoration:line-through; }
.pc-btns{ position:absolute; top:12px; right:12px; display:flex; gap:8px; }
.pc-btn{ padding:6px 12px; background:#1976d2; color:#fff; border:none; border-radius:6px; font-size:14px; cursor:pointer; }
.pc-btn.pc-clear{ background:#e53e3e; }
.pc-status{ margin-top:10px; font-weight:600; color:#1a7f37; }
.pc-canvas{ width:100%; height:560px; display:block; }
#plotConnectCanvas{ width:100%; height:560px; display:block; }

@media (max-width:600px){ .answers-grid { grid-template-columns:1fr; } }
</style>

<?php if ($sections !== null): /* ===== NEW: 1 question me teeno section ===== */ ?>

<div class="coord-wrap cp-multi">
    <?php if (!empty($q['question_text'])): ?>
        <div class="coord-title"><?= $h($q['question_text']) ?></div>
    <?php endif; ?>

    <?php if ($imgUrl !== ''): ?>
        <img src="<?= $h($imgUrl) ?>" alt="Coordinate Grid" class="coord-img" loading="lazy">
    <?php endif; ?>

    <?php foreach ($sections as $si => $sec):
        $stype    = $sec['type'] ?? 'coord_to_name';
        $secItems = $sec['items'] ?? [];
    ?>
        <div class="cp-section">
            <?php if (!empty($sec['heading'])): ?>
                <div class="cp-heading"><?= $h($sec['heading']) ?></div>
            <?php endif; ?>

            <?php if ($stype === 'coord_to_name' || $stype === 'name_to_coord'): ?>
                <div class="answers-grid">
                    <?php foreach ($secItems as $it):
                        $part = $it['part'] ?? ''; if ($part === '') continue;
                        $label = $it['label'] ?? '';
                        $coord = $it['coord'] ?? '';
                    ?>
                        <div class="answer-row">
                            <div class="answer-label"><?= $h($label) ?></div>
                            <?php if ($stype === 'coord_to_name'): ?>
                                <div class="coord-text"><?= $h($coord) ?></div>
                            <?php endif; ?>
                            <input type="text"
                                name="answer[<?= $qid ?>][<?= preg_replace('/[^a-zA-Z0-9_]/','',$part) ?>]"
                                class="answer-input"
                                placeholder="<?= ($stype==='name_to_coord') ? 'x,y' : '' ?>"
                                maxlength="<?= ($stype==='name_to_coord') ? 10 : 1 ?>">
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php elseif ($stype === 'plot' || $stype === 'coord_list' || $stype === 'plot_connect'): ?>
                <?php
                    $plotParts  = [];
                    $plotLabels = [];
                    $plotShapes = [];
                    $plotColors = [];
                    foreach ($secItems as $it) {
                        if (($it['part'] ?? '') === '') continue;
                        $plotParts[]  = $it['part'];
                        $plotLabels[] = (string)($it['label'] ?? '');
                        $plotShapes[] = (string)($it['shape'] ?? '');
                        $plotColors[] = (string)($it['color'] ?? '');
                    }
                    $canvasId = 'cpPlot_' . $qid . '_' . $si;
                    $connect  = !empty($sec['connect']);
                    $gm       = $gridMax;
                    $gmn      = $gridMin;
                ?>
                <div class="pc-instruction">List ke order me grid par click karke har point plot karo.</div>
                <div class="pc-coordlist">
                    <?php $ci=0; foreach ($secItems as $it): if (($it['part']??'')==='') continue; ?>
                        <span class="pc-chip" data-c="<?= $canvasId ?>" data-i="<?= $ci ?>">
                            <b><?= $h($it['label'] ?? '') ?></b> <?= $h($it['coord'] ?? '') ?>
                        </span>
                    <?php $ci++; endforeach; ?>
                </div>
                <div class="canvas-container">
                    <canvas id="<?= $canvasId ?>" class="pc-canvas"></canvas>
                    <div class="pc-btns">
                        <button type="button" class="pc-btn" data-undo="<?= $canvasId ?>">Undo</button>
                        <button type="button" class="pc-btn pc-clear" data-clear="<?= $canvasId ?>">Clear</button>
                    </div>
                </div>
                <div class="pc-status" id="<?= $canvasId ?>_status"></div>
                <?php foreach ($plotParts as $p): ?>
                    <input type="hidden" name="answer[<?= $qid ?>][<?= $h($p) ?>]" id="<?= $canvasId ?>_<?= $h($p) ?>">
                <?php endforeach; ?>

                               <script>
                (function(){
                    const PARTS  = <?= json_encode($plotParts) ?>;
                    const LABELS = <?= json_encode($plotLabels) ?>;
                    const SHAPES = <?= json_encode($plotShapes) ?>;
                    const COLORS = <?= json_encode($plotColors) ?>;
                    const GMIN = <?= (int)$gmn ?>;
                    const GMAX = <?= (int)$gm ?>;
                    const CONNECT = <?= $connect ? 'true' : 'false' ?>;
                    const CID = <?= json_encode($canvasId) ?>;
                    const canvas = document.getElementById(CID);
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');
                    const container = canvas.parentElement;
                    const dpr = window.devicePixelRatio || 1;
                    const cssWidth = container.clientWidth, cssHeight = 560;
                    canvas.style.width=cssWidth+'px'; canvas.style.height=cssHeight+'px';
                    canvas.width=cssWidth*dpr; canvas.height=cssHeight*dpr; ctx.setTransform(dpr,0,0,dpr,0,0);

                    const span = Math.max(1, GMAX - GMIN);
                    const area = { left:44, top:30, right:cssWidth-30, bottom:cssHeight-30 };
                    const gridSize = Math.floor(Math.min((area.right-area.left)/span,(area.bottom-area.top)/span));
                    area.right = area.left + gridSize*span;
                    area.top   = area.bottom - gridSize*span;

                    function px(gx){ return area.left + (gx - GMIN)*gridSize; }
                    function py(gy){ return area.bottom - (gy - GMIN)*gridSize; }

                    const plotted=[];

                    function drawGrid(){
                        ctx.clearRect(0,0,canvas.width,canvas.height);
                        ctx.strokeStyle='#e3e3e3'; ctx.lineWidth=1;
                        for(let i=GMIN;i<=GMAX;i++){
                            ctx.beginPath(); ctx.moveTo(px(i),py(GMIN)); ctx.lineTo(px(i),py(GMAX)); ctx.stroke();
                            ctx.beginPath(); ctx.moveTo(px(GMIN),py(i)); ctx.lineTo(px(GMAX),py(i)); ctx.stroke();
                        }
                        ctx.strokeStyle='#000'; ctx.lineWidth=2;
                        ctx.beginPath(); ctx.moveTo(px(GMIN),py(0)); ctx.lineTo(px(GMAX),py(0)); ctx.stroke();
                        ctx.beginPath(); ctx.moveTo(px(0),py(GMIN)); ctx.lineTo(px(0),py(GMAX)); ctx.stroke();

                        // ---- axis arrowheads (^ > < v) ----
                        arrowHead(px(0),   py(GMAX), 'up');
                        arrowHead(px(GMAX),py(0),    'right');
                        if(GMIN < 0){
                            arrowHead(px(0),   py(GMIN), 'down');
                            arrowHead(px(GMIN),py(0),    'left');
                        }

                        ctx.fillStyle='#333'; ctx.font='11px monospace';
                        ctx.textAlign='center'; ctx.textBaseline='top';
                        for(let i=GMIN;i<=GMAX;i++){ if(i===0) continue; ctx.fillText(i, px(i), py(0)+4); }
                        ctx.textAlign='right'; ctx.textBaseline='middle';
                        for(let i=GMIN;i<=GMAX;i++){ if(i===0) continue; ctx.fillText(i, px(0)-5, py(i)); }
                    }
                    function star(cx,cy,spikes,outer,inner){
                        let rot=-Math.PI/2, step=Math.PI/spikes; ctx.beginPath();
                        for(let i=0;i<spikes;i++){
                            ctx.lineTo(cx+Math.cos(rot)*outer, cy+Math.sin(rot)*outer); rot+=step;
                            ctx.lineTo(cx+Math.cos(rot)*inner, cy+Math.sin(rot)*inner); rot+=step;
                        }
                        ctx.closePath(); ctx.fill();
                    }
                    function arrowHead(x,y,dir){
                        const s=7; ctx.fillStyle='#000'; ctx.beginPath();
                        if(dir==='up'){    ctx.moveTo(x,y-s); ctx.lineTo(x-5,y+3); ctx.lineTo(x+5,y+3); }
                        else if(dir==='down'){  ctx.moveTo(x,y+s); ctx.lineTo(x-5,y-3); ctx.lineTo(x+5,y-3); }
                        else if(dir==='right'){ ctx.moveTo(x+s,y); ctx.lineTo(x-3,y-5); ctx.lineTo(x-3,y+5); }
                        else if(dir==='left'){  ctx.moveTo(x-s,y); ctx.lineTo(x+3,y-5); ctx.lineTo(x+3,y+5); }
                        ctx.closePath(); ctx.fill();
                    }
                    function drawShape(type, X, Y, s, color){
                        const col = color || '#d00'; ctx.fillStyle=col; ctx.strokeStyle=col;
                        switch(type){
                            case 'circle':    ctx.beginPath(); ctx.arc(X,Y,s,0,Math.PI*2); ctx.fill(); break;
                            case 'square':    ctx.fillRect(X-s, Y-s, s*2, s*2); break;
                            case 'rectangle': ctx.fillRect(X-s*1.4, Y-s*0.75, s*2.8, s*1.5); break;
                            case 'triangle':  ctx.beginPath(); ctx.moveTo(X, Y-s); ctx.lineTo(X+s, Y+s); ctx.lineTo(X-s, Y+s); ctx.closePath(); ctx.fill(); break;
                            case 'star':      star(X, Y, 5, s, s*0.45); break;
                            default:          ctx.beginPath(); ctx.arc(X,Y,4,0,Math.PI*2); ctx.fill();
                        }
                    }
                    function drawPts(){
                        if(CONNECT && plotted.length>1){
                            ctx.strokeStyle='#1976d2'; ctx.lineWidth=2.5; ctx.beginPath();
                            plotted.forEach((p,idx)=>{ const X=px(p.gx), Y=py(p.gy); if(idx===0) ctx.moveTo(X,Y); else ctx.lineTo(X,Y); });
                            ctx.stroke();
                        }
                        plotted.forEach((p,idx)=>{
                            const X=px(p.gx), Y=py(p.gy);
                            const shp=(SHAPES[idx]||'').toLowerCase();
                            if(shp) drawShape(shp,X,Y,11,COLORS[idx]||'');
                            else { ctx.fillStyle='#d00'; ctx.beginPath(); ctx.arc(X,Y,4,0,Math.PI*2); ctx.fill(); }
                            ctx.fillStyle='#111'; ctx.font='bold 11px sans-serif'; ctx.textAlign='center'; ctx.textBaseline='bottom';
                            ctx.fillText((LABELS[idx]||(idx+1)), X, Y-13);
                        });
                    }
                    function status(){
                        const s=document.getElementById(CID+'_status'); if(!s) return;
                        s.textContent = (plotted.length>=PARTS.length) ? '✅ All points plotted!' : ('Now plot: ' + (LABELS[plotted.length] || ('point '+(plotted.length+1))));
                    }
                    function redraw(){
                        drawGrid(); drawPts(); status();
                        document.querySelectorAll('.pc-chip[data-c="'+CID+'"]').forEach((c,i)=>{
                            c.classList.toggle('active', i===plotted.length);
                            c.classList.toggle('done', i<plotted.length);
                        });
                    }
                    canvas.addEventListener('click',function(e){
                        if(plotted.length>=PARTS.length) return;
                        const r=canvas.getBoundingClientRect();
                        const gx=Math.round((e.clientX-r.left-area.left)/gridSize)+GMIN;
                        const gy=Math.round((area.bottom-(e.clientY-r.top))/gridSize)+GMIN;
                        if(gx<GMIN||gx>GMAX||gy<GMIN||gy>GMAX) return;
                        plotted.push({gx,gy});
                        const inp=document.getElementById(CID+'_'+PARTS[plotted.length-1]);
                        if(inp) inp.value=gx+','+gy;
                        redraw();
                    });
                    const ub=document.querySelector('[data-undo="'+CID+'"]');
                    if(ub) ub.addEventListener('click',function(){ if(!plotted.length) return; plotted.pop();
                        const inp=document.getElementById(CID+'_'+PARTS[plotted.length]); if(inp) inp.value=''; redraw(); });
                    const cb=document.querySelector('[data-clear="'+CID+'"]');
                    if(cb) cb.addEventListener('click',function(){ plotted.length=0;
                        PARTS.forEach(p=>{const i=document.getElementById(CID+'_'+p); if(i) i.value='';}); redraw(); });
                    redraw();
                })();
                </script>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php else: /* ===== PURANA single-mode (backward compatible) ===== */ ?>

<div class="coord-wrap <?= $isQue3 ? 'que3' : '' ?> <?= $isPlotConnect ? 'plotconnect' : '' ?>">
    <?php if (!empty($q['question_text'])): ?>
        <div class="coord-title"><?= $h($q['question_text']) ?></div>
    <?php endif; ?>

    <?php if ($isPlotConnect): ?>
        <?php
        $pcParts = [];
        foreach ($items as $item) {
            if (($item['type'] ?? '') !== 'plot_connect') continue;
            if (($item['part'] ?? '') === '') continue;
            $pcParts[] = $item['part'];
        }
        ?>
        <div class="pc-instruction">Start with Point <b>1</b> — click and plot the points on the grid in order. The line connects automatically.</div>
        <div class="pc-coordlist">
            <?php $n=1; foreach ($items as $item):
                if (($item['type'] ?? '') !== 'plot_connect') continue;
                $lab = $item['label'] ?? $n; ?>
                <span class="pc-chip" data-i="<?= $n-1 ?>"><b><?= $h($lab) ?>)</b> <?= $h($item['coord'] ?? '') ?></span>
            <?php $n++; endforeach; ?>
        </div>
        <div class="canvas-container">
            <canvas id="plotConnectCanvas"></canvas>
            <div class="pc-btns">
                <button type="button" class="pc-btn" id="pcUndo">Undo</button>
                <button type="button" class="pc-btn pc-clear" id="pcClear">Clear</button>
            </div>
        </div>
        <div class="pc-status" id="pcStatus"></div>
        <?php foreach ($pcParts as $p): ?>
            <input type="hidden" name="answer[<?= $qid ?>][<?= $h($p) ?>]" id="pc_<?= $h($p) ?>">
        <?php endforeach; ?>
        <script>
        (function(){
          const PARTS = <?= json_encode($pcParts) ?>;
          const GRID_MAX = <?= (int)$gridMax ?>;
          const canvas = document.getElementById('plotConnectCanvas');
          if (!canvas) return;
          const ctx = canvas.getContext('2d');
          const container = canvas.parentElement;
          const dpr = window.devicePixelRatio || 1;
          const cssWidth = container.clientWidth, cssHeight = 560;
          canvas.style.width=cssWidth+'px'; canvas.style.height=cssHeight+'px';
          canvas.width=cssWidth*dpr; canvas.height=cssHeight*dpr; ctx.setTransform(dpr,0,0,dpr,0,0);
          const margin=50, topMargin=40, rightMargin=30;
          const area={left:margin, top:topMargin, right:cssWidth-rightMargin, bottom:cssHeight-margin};
          const gridSize=Math.floor(Math.min((area.right-area.left)/GRID_MAX,(area.bottom-area.top)/GRID_MAX));
          area.right=area.left+gridSize*GRID_MAX; area.top=area.bottom-gridSize*GRID_MAX;
          const plotted=[];
          function drawGrid(){
            ctx.clearRect(0,0,canvas.width,canvas.height);
            ctx.strokeStyle='#ddd'; ctx.lineWidth=1;
            for(let i=0;i<=GRID_MAX;i++){ const x=area.left+i*gridSize;
              ctx.beginPath(); ctx.moveTo(x,area.top); ctx.lineTo(x,area.bottom); ctx.stroke();
              const y=area.bottom-i*gridSize; ctx.beginPath(); ctx.moveTo(area.left,y); ctx.lineTo(area.right,y); ctx.stroke(); }
            ctx.strokeStyle='#000'; ctx.lineWidth=2;
            ctx.beginPath(); ctx.moveTo(area.left,area.bottom); ctx.lineTo(area.right+6,area.bottom); ctx.stroke();
            ctx.beginPath(); ctx.moveTo(area.left,area.bottom); ctx.lineTo(area.left,area.top-6); ctx.stroke();
            ctx.fillStyle='#000'; ctx.font='12px monospace';
            ctx.textAlign='center'; ctx.textBaseline='top';
            for(let i=0;i<=GRID_MAX;i++) ctx.fillText(i, area.left+i*gridSize, area.bottom+6);
            ctx.textAlign='right'; ctx.textBaseline='middle';
            for(let i=0;i<=GRID_MAX;i++) ctx.fillText(i, area.left-6, area.bottom-i*gridSize);
          }
          function drawPath(){
            if(plotted.length>1){ ctx.strokeStyle='#1976d2'; ctx.lineWidth=2.5; ctx.beginPath();
              plotted.forEach((p,idx)=>{ const X=area.left+p.gx*gridSize, Y=area.bottom-p.gy*gridSize;
                if(idx===0) ctx.moveTo(X,Y); else ctx.lineTo(X,Y); }); ctx.stroke(); }
            plotted.forEach((p,idx)=>{ const X=area.left+p.gx*gridSize, Y=area.bottom-p.gy*gridSize;
              ctx.fillStyle='#d00'; ctx.beginPath(); ctx.arc(X,Y,4,0,Math.PI*2); ctx.fill();
              ctx.fillStyle='#111'; ctx.font='bold 11px monospace'; ctx.textAlign='left'; ctx.textBaseline='bottom';
              ctx.fillText(idx+1, X+5, Y-3); });
          }
          function status(){ const s=document.getElementById('pcStatus'); if(!s) return;
            s.textContent = (plotted.length>=PARTS.length) ? '✅ All points have been plotted!' : ('Now plot Point '+(plotted.length+1)+'.'); }
          function redraw(){ drawGrid(); drawPath(); status();
            document.querySelectorAll('.pc-chip').forEach((c,i)=>{ c.classList.toggle('active', i===plotted.length); c.classList.toggle('done', i<plotted.length); }); }
          canvas.addEventListener('click',function(e){
            if(plotted.length>=PARTS.length) return;
            const r=canvas.getBoundingClientRect();
            const gx=Math.round((e.clientX-r.left-area.left)/gridSize);
            const gy=Math.round((area.bottom-(e.clientY-r.top))/gridSize);
            if(gx<0||gx>GRID_MAX||gy<0||gy>GRID_MAX) return;
            plotted.push({gx,gy});
            const inp=document.getElementById('pc_'+PARTS[plotted.length-1]); if(inp) inp.value=gx+','+gy;
            redraw();
          });
          document.getElementById('pcUndo').addEventListener('click',function(){ if(!plotted.length) return; plotted.pop();
            const inp=document.getElementById('pc_'+PARTS[plotted.length]); if(inp) inp.value=''; redraw(); });
          document.getElementById('pcClear').addEventListener('click',function(){ plotted.length=0;
            PARTS.forEach(p=>{const i=document.getElementById('pc_'+p); if(i) i.value='';}); redraw(); });
          redraw();
        })();
        </script>

    <?php elseif ($imgUrl !== '' && !$isQue3): ?>
        <img src="<?= $h($imgUrl) ?>" alt="Coordinate Grid" class="coord-img" loading="lazy">

    <?php elseif ($isQue3): ?>
        <div class="letter-picker" style="margin:10px 0; display:flex; gap:10px; flex-wrap:wrap;">
          <?php foreach ($items as $item):
            if (($item['type'] ?? '') !== 'coord_list') continue;
            preg_match('/^([A-Z])\(/', $item['coord'], $m); $label = $m[1] ?? '';
          ?>
            <button type="button" class="letter-btn" data-letter="<?= $h($label) ?>"
              style="padding:6px 12px;border:2px solid #1976d2;border-radius:6px;background:#fff;color:#1976d2;font-weight:600;">
              <?= $h($label) ?>
            </button>
          <?php endforeach; ?>
        </div>
        <div class="canvas-container">
            <canvas id="drawCanvas"></canvas>
            <button type="button" class="clear-btn" onclick="clearCanvas()">Clear</button>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            let ACTIVE_LETTER = null;
            document.querySelectorAll('.letter-btn').forEach(btn => {
              btn.addEventListener('click', () => {
                document.querySelectorAll('.letter-btn').forEach(b => { b.style.background='#fff'; b.style.color='#1976d2'; });
                btn.style.background='#1976d2'; btn.style.color='#fff'; ACTIVE_LETTER = btn.dataset.letter;
              });
            });
            const canvas = document.getElementById('drawCanvas'); const ctx = canvas.getContext('2d');
            const container = canvas.parentElement; const dpr = window.devicePixelRatio || 1;
            const cssWidth = container.clientWidth, cssHeight = 700;
            canvas.style.width=cssWidth+'px'; canvas.style.height=cssHeight+'px';
            canvas.width=cssWidth*dpr; canvas.height=cssHeight*dpr; ctx.setTransform(dpr,0,0,dpr,0,0);
            const GRID_MAX = <?= (int)$gridMax ?>;
            const margin=60, topMargin=90, rightMargin=70;
            const drawArea={left:margin, top:topMargin, right:cssWidth-rightMargin, bottom:cssHeight-margin};
            const gridSize=Math.floor(Math.min((drawArea.right-drawArea.left)/GRID_MAX,(drawArea.bottom-drawArea.top)/GRID_MAX));
            drawArea.right=drawArea.left+gridSize*GRID_MAX; drawArea.top=drawArea.bottom-gridSize*GRID_MAX;
            ctx.strokeStyle='#ddd'; ctx.lineWidth=1;
            for(let x=drawArea.left;x<=drawArea.right;x+=gridSize){ ctx.beginPath(); ctx.moveTo(x,drawArea.top); ctx.lineTo(x,drawArea.bottom); ctx.stroke(); }
            for(let y=drawArea.top;y<=drawArea.bottom;y+=gridSize){ ctx.beginPath(); ctx.moveTo(drawArea.left,y); ctx.lineTo(drawArea.right,y); ctx.stroke(); }
            ctx.strokeStyle='#000'; ctx.lineWidth=2;
            ctx.beginPath(); ctx.moveTo(drawArea.left,drawArea.bottom); ctx.lineTo(drawArea.right,drawArea.bottom);
            ctx.lineTo(drawArea.right-10,drawArea.bottom-10); ctx.moveTo(drawArea.right,drawArea.bottom); ctx.lineTo(drawArea.right-10,drawArea.bottom+10); ctx.stroke();
            ctx.beginPath(); ctx.moveTo(drawArea.left,drawArea.bottom); ctx.lineTo(drawArea.left,drawArea.top-20);
            ctx.lineTo(drawArea.left-8,drawArea.top); ctx.moveTo(drawArea.left,drawArea.top-20); ctx.lineTo(drawArea.left+8,drawArea.top); ctx.stroke();
            ctx.font='14px monospace'; ctx.fillStyle='#000';
            ctx.textAlign='center'; ctx.textBaseline='top';
            for(let i=0;i<=GRID_MAX;i++){ ctx.fillText(i, drawArea.left+i*gridSize, drawArea.bottom+8); }
            ctx.fillText('x-axis', drawArea.right-20, drawArea.bottom+25);
            ctx.textAlign='right'; ctx.textBaseline='middle';
            for(let i=0;i<=GRID_MAX;i++){ ctx.fillText(i, drawArea.left-8, drawArea.bottom-i*gridSize); }
            ctx.save(); ctx.translate(15,80); ctx.rotate(-Math.PI/2); ctx.fillText('y-axis',0,0); ctx.restore();
            canvas.addEventListener('click', (e) => {
                if (!ACTIVE_LETTER) { alert('Please select a letter first'); return; }
                const rect=canvas.getBoundingClientRect(); const xPix=e.clientX-rect.left, yPix=e.clientY-rect.top;
                if(xPix<drawArea.left||xPix>drawArea.right||yPix<drawArea.top||yPix>drawArea.bottom) return;
                const gridX=Math.round((xPix-drawArea.left)/gridSize); const gridY=Math.round((drawArea.bottom-yPix)/gridSize);
                if(gridX<0||gridX>GRID_MAX||gridY<0||gridY>GRID_MAX) return;
                const drawX=drawArea.left+gridX*gridSize, drawY=drawArea.bottom-gridY*gridSize;
                ctx.strokeStyle='#d00'; ctx.lineWidth=2; ctx.beginPath(); ctx.arc(drawX,drawY,6,0,Math.PI*2); ctx.stroke();
                ctx.fillStyle='#d00'; ctx.beginPath(); ctx.arc(drawX,drawY,2.5,0,Math.PI*2); ctx.fill();
                document.querySelectorAll('.coord-text').forEach(el => {
                    const full=el.textContent.replace(/\s/g,''); const letter=full.charAt(0);
                    if (ACTIVE_LETTER && letter===ACTIVE_LETTER) {
                        const row=el.closest('.answer-row'); const input=row.querySelector('.answer-input'); if(input) input.value=ACTIVE_LETTER;
                    }
                });
            });
            window.clearCanvas = function(){ ctx.clearRect(0,0,canvas.width,canvas.height); const ev=new Event('DOMContentLoaded'); document.dispatchEvent(ev); };
        });
        </script>

    <?php else: ?>
        <div style="height:400px; border:2px dashed #eee; border-radius:12px; display:flex; align-items:center; justify-content:center; color:#ccc;">
            [No Image]
        </div>
    <?php endif; ?>

    <?php if (!$isPlotConnect): ?>
    <div class="answers-grid">
        <?php
        $counter = 1;
        foreach ($items as $item):
            $part = $item['part'] ?? ''; $label = $item['label'] ?? '';
            $coord = $item['coord'] ?? ''; $type = $item['type'] ?? 'coord_to_name';
            if (!$part) continue;
            $displayLabel = $label;
            if ($type === 'coord_to_name' && $label === '') { $displayLabel = $counter++; }
        ?>
            <div class="answer-row">
                <div class="answer-label"><?= $h($displayLabel) ?></div>
                <?php if ($type === 'coord_to_name' || $type === 'coord_list'): ?>
                    <div class="coord-text"><?= $h($coord) ?></div>
                <?php endif; ?>
                <input type="text"
                    name="answer[<?= $qid ?>][<?= preg_replace('/[^a-zA-Z0-9_]/','',$part) ?>]"
                    class="answer-input"
                    placeholder="<?= ($type === 'name_to_coord') ? 'x,y' : '' ?>"
                    maxlength="<?= ($type === 'name_to_coord') ? 10 : 1 ?>">
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php endif; ?>