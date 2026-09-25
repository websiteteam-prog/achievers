<?php
declare(strict_types=1);
$q = $q ?? [];
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$type = $q['question_type'] ?? '';
$inst_id = (int)($q['instruction_id'] ?? 0);
$id = (int)($q['id'] ?? 0);
$root = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
$makeImg = function($img) use ($root) {
    if (!$img) return '';
    if (preg_match('~^https?://~i', $img)) return $img;   // already full URL
    return ($root === '' ? '' : $root) . '/' . ltrim($img, '/');
};
$GLOBALS['__match_net_index'] = $GLOBALS['__match_net_index'] ?? 0;
?>
<style>
.solid-index{
    position:absolute;
    left:12px;
    top:12px;
    font-weight:700;
    font-size:18px;
}
.match-solid{position:relative}

/* shared */
.shape-container{display:flex;flex-direction:row;align-items:center;gap:12px}
.shape-img-wrapper{width:45%;display:flex;justify-content:center}
.answer-box{width:55%}
/* table */
.table-row{display:flex;align-items:center;margin-bottom:25px;border:1px solid #ddd;padding:10px;border-radius:6px}
.table-row img{width:120px;height:auto;margin-right:20px}
.table-input-box{flex:1}
.table-field{display:flex;align-items:center;margin-bottom:8px}
.table-field span{width:80px;font-weight:600}
.table-field input{width:80px;margin-left:10px}
/* matching */
.match-board{position:relative}
.match-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:25px;padding:12px;border:1px solid #ddd;border-radius:6px;position:relative}
.match-solid,.match-net{width:48%;text-align:center;cursor:crosshair}
.match-solid img,.match-net img{max-width:150px;height:auto}
.net-label{font-weight:600;display:block;margin-bottom:5px;font-size:18px}

/* ---- NEW: Clear Lines button (left side) ---- */
.clear-lines-btn{
  position:absolute; right:8px;
  left:auto; top:50%; transform:translateY(-50%);
  background:#e74c3c; color:#fff; border:none; border-radius:4px;
  padding:4px 8px; font-size:12px; cursor:pointer; z-index:10;
}
.clear-lines-btn:hover{background:#c0392b}

/* global SVG canvas for lines */
#matchCanvasGlobal{
  position:absolute;
  top:0;
  left:0;
  width:100%;
  height:100%;
  pointer-events:none;
  z-index:9999;
}

/* drag preview */
.line-preview{stroke:#2f80ed;stroke-width:3;fill:none;stroke-linecap:round;stroke-dasharray:6 6}
.line-final{stroke:#2f80ed;stroke-width:3;fill:none;stroke-linecap:round}
.drag-active .match-solid, .drag-active .match-net{cursor:crosshair}
</style>
<?php if ($type === 'question_renderer'): ?>
  <div class="col-md-4 mb-3">
    <div class="shape-container">
      <div class="shape-img-wrapper">
        <img src="<?= $h($makeImg($q['question_image'] ?? '')) ?>" alt="Shape" class="shape-img">
      </div>
      <div class="answer-box">
        <input type="text" class="form-control"
               name="answer[<?= $id ?>]"
               placeholder="Prism or Pyramid">
        <!--<input type="hidden"-->
        <!--       name="answer[<?= $id ?>]"-->
        <!--       value="<?= $h($q['correct_answer'] ?? '') ?>">-->
      </div>
    </div>
  </div>
<?php elseif ($type === 'complete_table'): ?>
  <div class="col-md-12">
    <div class="table-row">
      <?php if (!empty($q['question_image'])): ?>
        <img src="<?= $h($makeImg($q['question_image'])) ?>" alt="Solid">
      <?php endif; ?>
      <div class="table-input-box">
        <?php
        $payload = json_decode($q['question_payload'] ?? '{}', true) ?: [];
        foreach (['Faces','Edges','Vertices'] as $field):
          $key = strtolower($field);
          $correct = $payload[$key] ?? '';
        ?>
          <div class="table-field">
            <span><?= $field ?> =</span>
            <input type="text" class="form-control"
                   name="answer[<?= $id ?>][<?= $key ?>]" style="width:80px;">
            <!--<input type="hidden"-->
            <!--       name="answer[<?= $id ?>][<?= $key ?>]"-->
            <!--       value="<?= $h($correct) ?>">-->
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
<?php elseif ($type === 'match_nets'): ?>
  <?php
    $payload = json_decode($q['question_payload'] ?? '{}', true) ?: [];
    $netImg = $makeImg($payload['net_image'] ?? '');
    $label = $payload['label'] ?? '';
    // stable IDs used by JS for hit-testing and saving pairs
    $solidId = "solid_{$inst_id}_{$id}";
    $netId = "net_{$inst_id}_{$id}";
    // unique ID for this row’s clear button
    $clearBtnId = "clearBtn_{$inst_id}_{$id}";
  ?>
  <!-- a single global canvas for the whole page (created once) -->
  <script>
    (function ensureGlobalCanvas(){
      if (!document.getElementById('matchCanvasGlobal')) {
        const svg = document.createElementNS('http://www.w3.org/2000/svg','svg');
        svg.setAttribute('id','matchCanvasGlobal');
        document.body.appendChild(svg);
      }
    })();
  </script>
  <div class="col-md-12 match-board">
    <div class="match-row" data-row-id="row_<?= $inst_id ?>_<?= $id ?>">
      <!-- Clear Lines button (left side) -->
      <button type="button" class="clear-lines-btn" style="top:10%" id="<?= $h($clearBtnId) ?>">Clear</button>

   <div class="match-solid"
     id="<?= $h($solidId) ?>"
     data-qid="<?= $id ?>"
     data-label="<?= $h($q['correct_answer']) ?>">

    <span class="solid-index">
        <?= ++$GLOBALS['__match_net_index'] ?>)
    </span>

    <img src="<?= $h($makeImg($q['question_image'] ?? '')) ?>" alt="Solid">
</div>

     <div class="match-net"
     id="<?= $h($netId) ?>"
     data-label="<?= $h($payload['label'] ?? '') ?>">

    <?php if ($netImg): ?>
        <img src="<?= $h($netImg) ?>" alt="Net">
    <?php elseif (!empty($payload['net_text'])): ?>
        <span class="match-net-text" style="font-size:20px;font-weight:700;color:#222;"><?= $h($payload['net_text']) ?></span>
    <?php endif; ?>
</div>

    </div>
  </div>
  <!-- matches collected for form submit -->
  <div id="matchHiddenContainer_<?= $id ?>" style="display:none;"></div>

   <script>
  (function(){
    if (window.__matchInit) return;
    window.__matchInit = true;

    const svgNS='http://www.w3.org/2000/svg';
    const svg = ()=>document.getElementById('matchCanvasGlobal');
    // har shape ki line ka alag color
    const PALETTE=['#e6194b','#3cb44b','#4363d8','#f58231','#911eb4','#008080','#f032e6','#9a6324','#808000','#000075','#e8862e'];

    function sizeCanvas(){
      const s=svg(); if(!s) return;
      const w=document.documentElement.clientWidth;                              // viewport width (koi extra scroll nahi)
      const h=Math.max(document.documentElement.scrollHeight, document.documentElement.clientHeight);
      s.setAttribute('width',w); s.setAttribute('height',h);
      s.style.width=w+'px'; s.style.height=h+'px'; s.style.left='0'; s.style.top='0';
    }
    // line ko shape/naam ke asli KINARE se jodo (beech se na kaate)
    function innerOf(el){ return el.querySelector('img, .match-net-text') || el; }
    function anchorFrom(el){ const r=innerOf(el).getBoundingClientRect(); return {x:r.right+window.scrollX, y:r.top+r.height/2+window.scrollY}; } // shape ka right
    function anchorTo(el){   const r=innerOf(el).getBoundingClientRect(); return {x:r.left +window.scrollX, y:r.top+r.height/2+window.scrollY}; } // naam ka left
    function centerOf(el){   const r=el.getBoundingClientRect(); return {x:r.left+r.width/2+window.scrollX, y:r.top+r.height/2+window.scrollY}; }
    function setLine(l,a,b,preview){ l.setAttribute('x1',a.x);l.setAttribute('y1',a.y);l.setAttribute('x2',b.x);l.setAttribute('y2',b.y);l.setAttribute('class',preview?'line-preview':'line-final'); }
    function colorFor(fromEl){ const list=Array.from(document.querySelectorAll('.match-solid')); const i=list.indexOf(fromEl); return PALETTE[(i<0?0:i)%PALETTE.length]; }

    const connections=[]; let drag=null;

    function saveAnswer(solidEl, netLabel){
      const qid=solidEl.dataset.qid;
      const box=document.getElementById('matchHiddenContainer_'+qid);
      if(!box) return; box.innerHTML='';
      const inp=document.createElement('input');
      inp.type='hidden'; inp.name='answer['+qid+']'; inp.value=netLabel;   // ✅ connected NAAM ka label
      box.appendChild(inp);
    }
    function clearAnswer(solidEl){ const b=document.getElementById('matchHiddenContainer_'+solidEl.dataset.qid); if(b) b.innerHTML=''; }
    function rowIdOf(el){ const r=el.closest('.match-row'); return r?r.dataset.rowId:''; }
    function removeConn(pred){
      for(let i=connections.length-1;i>=0;i--){
        if(pred(connections[i])){ clearAnswer(connections[i].fromEl); connections[i].lineEl.remove(); connections.splice(i,1); }
      }
    }
    // drop point ke sabse paas wala naam (exact text par drop na ho to bhi jud jaaye)
    function nearestNet(px,py){
      let best=null, bd=Infinity;
      document.querySelectorAll('.match-net').forEach(n=>{ const c=centerOf(n); const dx=c.x-px,dy=c.y-py,d=dx*dx+dy*dy; if(d<bd){bd=d;best=n;} });
      return (best && bd <= 300*300) ? best : null;   // 300px tak forgiving
    }

    document.addEventListener('mousedown', function(ev){
      const fromEl = ev.target.closest ? ev.target.closest('.match-solid') : null;
      if(!fromEl) return;
      ev.preventDefault();
      removeConn(c=>c.fromEl===fromEl);           // ek shape = ek line
      const s=svg(); if(!s) return;
      const col=colorFor(fromEl);
      const line=document.createElementNS(svgNS,'line'); s.appendChild(line);
      line.style.stroke=col; line.style.strokeWidth='3'; line.style.strokeLinecap='round';
      setLine(line, anchorFrom(fromEl), {x:ev.pageX,y:ev.pageY}, true);
      drag={fromEl, lineEl:line, col};
      document.body.classList.add('drag-active');
    });

    document.addEventListener('mousemove', function(ev){ if(drag) setLine(drag.lineEl, anchorFrom(drag.fromEl), {x:ev.pageX,y:ev.pageY}, true); });

    document.addEventListener('mouseup', function(ev){
      if(!drag) return;
      let toEl=null;
      const t=document.elementFromPoint(ev.clientX,ev.clientY);
      if(t && t.closest) toEl=t.closest('.match-net');
      if(!toEl) toEl=nearestNet(ev.pageX,ev.pageY);     // paas chhodo to bhi connect
      if(toEl){
        removeConn(c=>c.toEl===toEl);                   // ek naam par ek hi line
        setLine(drag.lineEl, anchorFrom(drag.fromEl), anchorTo(toEl), false);
        connections.push({fromEl:drag.fromEl, toEl, lineEl:drag.lineEl, fromRow:rowIdOf(drag.fromEl), toRow:rowIdOf(toEl)});
        saveAnswer(drag.fromEl, toEl.dataset.label||'');
        const line=drag.lineEl; line.style.pointerEvents='stroke';
        line.addEventListener('contextmenu', function(e){ e.preventDefault(); removeConn(c=>c.lineEl===line); }); // right-click delete
      } else { drag.lineEl.remove(); }
      drag=null; document.body.classList.remove('drag-active');
    });

    // Clear: is row ko chhoone waali koi bhi line hatao (shape wali bhi + naam wali bhi)
    document.addEventListener('click', function(e){
      const btn=e.target.closest ? e.target.closest('.clear-lines-btn') : null;
      if(!btn) return;
      const row=btn.closest('.match-row'); if(!row) return;
      const rid=row.dataset.rowId;
      removeConn(c=>c.fromRow===rid || c.toRow===rid);
    });

    function redrawAll(){ sizeCanvas(); connections.forEach(c=>setLine(c.lineEl, anchorFrom(c.fromEl), anchorTo(c.toEl), false)); }
    window.addEventListener('scroll', redrawAll, {passive:true});
    window.addEventListener('resize', redrawAll);
    window.addEventListener('load', redrawAll);
    sizeCanvas();
  })();
  </script>
<?php endif; ?>