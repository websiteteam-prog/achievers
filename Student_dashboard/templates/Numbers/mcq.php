<?php

$payload = json_decode($q['question_payload'] ?? '{}', true);
$payload = is_array($payload) ? $payload : [];

$options = $payload['options'] ?? [];

$isMultiple = (($payload['selection'] ?? '') === 'multiple');

$image_path = trim((string)($q['question_image'] ?? ''));

/*
|--------------------------------------------------------------------------
| Image URL
|--------------------------------------------------------------------------
*/

$protocol = (
    !empty($_SERVER['HTTPS']) &&
    $_SERVER['HTTPS'] !== 'off'
) ? 'https' : 'http';

$domain = $_SERVER['HTTP_HOST'];
$base_path = '/Student_dashboard/';

$final_image_path = $protocol . '://' .
    $domain .
    $base_path .
    ltrim($image_path, '/');

?>

<style>

.mcq-card{
    background:#fff;
    padding:22px 28px;
    margin:15px 0 22px;
    border-radius:14px;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
}

/* Question */
.mcq-question{
    font-size:18px;
    font-weight:600;
    line-height:1.6;
    color:#111;
    margin-bottom:18px;
}

/* Diagram */
.mcq-image-box{
    width: 100%;
    max-width: 300px;
    height: 150px;

    margin: 10px 0 15px;

    display: flex;
    justify-content: flex-start;
    align-items: center;
}

.mcq-image-box img{
    max-width: 300px;
    max-height: 150px;

    width: auto;
    height: auto;

    object-fit: contain;
    object-position: left center;

    display: block;
}

/* Options */
.option-grid{
    display:grid;
    grid-template-columns:repeat(2, minmax(220px, 1fr));
    gap:14px 30px;
    margin-top:15px;
}

.option{
    display:flex;
    align-items:center;
    gap:9px;
    font-size:17px;
    cursor:pointer;
}

.option input{
    width:18px;
    height:18px;
    cursor:pointer;
    flex-shrink:0;
}

@media(max-width:768px){

    .mcq-card{
        padding:18px;
    }

    .option-grid{
        grid-template-columns:1fr;
    }

    .mcq-question{
        font-size:17px;
    }

    .mcq-image-box{
        max-width:220px;
        height:120px;
    }

    .mcq-image-box img{
        max-width:220px;
        max-height:120px;
    }

}

</style>


<div class="mcq-card">

    <!-- QUESTION -->
    <div class="mcq-question">

        <?= ($index + 1) ?>)
        <?= htmlspecialchars(
            $q['question_text'] ?? '',
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        ) ?>

    </div>


    <!-- DIAGRAM / IMAGE -->
    <?php if ($image_path !== ''): ?>

        <div class="mcq-image-box">

            <img
                src="<?= htmlspecialchars(
                    $final_image_path,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ) ?>"
                alt="Question diagram"
            >

        </div>

    <?php endif; ?>


    <!-- OPTIONS -->
    <div class="option-grid">

        <?php foreach ($options as $option): ?>

            <label class="option">

                <input
                    type="<?= $isMultiple ? 'checkbox' : 'radio' ?>"
                    name="answer[<?= (int)$q['id'] ?>]<?= $isMultiple ? '[]' : '' ?>"
                    value="<?= htmlspecialchars(
                        $option,
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        'UTF-8'
                    ) ?>"
                >

                <span>
                    <?= htmlspecialchars(
                        $option,
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        'UTF-8'
                    ) ?>
                </span>

            </label>

        <?php endforeach; ?>

    </div>

</div>