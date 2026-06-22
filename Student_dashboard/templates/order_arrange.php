<style>
    .container-fluid {
        margin-left: 50px;
        margin-bottom: 20px;
        padding: 15px 20px;
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        background-color: #ffffff;
        transition: box-shadow 0.3s, transform 0.2s;
        width: 90%;
        margin-top: 10px;
    }

    .container-fluid:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .container-fluid h6 {
        font-weight: 600;
        margin-bottom: 10px;
        color: #333;
    }

    .arrange-container {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .arrange-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .arrange-input {
        width: 80px;
        text-align: center;
        font-size: 16px;
        padding: 6px;
        border: none;
        border-bottom: 2px solid #ccc;
        background: transparent;
        transition: border-color 0.3s;
    }

    .arrange-input:focus {
        border-bottom-color: #007bff;
        outline: none;
    }

    .less-symbol {
        font-size: 18px;
        font-weight: 600;
        color: #555;
    }

    @media (max-width:768px) {
        .container-fluid {
            margin-left: 0;
            width: 95%;
        }

        .arrange-container {
            justify-content: center;
        }
    }
</style>

<div class="container-fluid col-lg-12 col-md-12 col-sm-12">

    <h6><?= $char . '. ' . htmlspecialchars($q['question_text']) ?></h6>
    <?php $char++; ?>

    <?php
    $numbers = explode(',', $q['question_payload']);
    $total = count($numbers);
    ?>

    <div class="arrange-container">

        <?php for ($i = 0; $i < $total; $i++) { ?>

            <div class="arrange-item">

                <input
                    type="text"
                    class="arrange-input"
                    name="answer[<?= $q['id'] ?>][]"
                    placeholder="______">

            </div>

            <?php if ($i < $total - 1) { ?>
                <span class="less-symbol">&lt;</span>
            <?php } ?>

        <?php } ?>

    </div>

</div>