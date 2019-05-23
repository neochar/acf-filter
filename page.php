<div>
    <h2>Данные</h2>
    <form method="post" action="<?= admin_url('admin-post.php') ?>">
        <input type="hidden" name="action" value="acf_filter_generate_demo"/>
        <button>Сгенерировать Demo данные</button>
    </form>

    <h2>Форма фильтрации</h2>
    <form method="post" action="<?= admin_url('admin-post.php') ?>">
        <input type="hidden" name="action" value="acf_filter_test_filter"/>
        <div>
            <select
                    multiple
                    name="cities[]"
                    size="<?= count($cities) ?>"
                    style="height: auto"
            >
                <?php foreach ($cities as $city) : ?>
                    <option value="<?= $city ?>">
                        <?= $city ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <ul>
                <?php foreach ($choices_1 as $choice) : ?>
                    <li>
                        <label>
                            <input type="checkbox"
                                   name="option_1[<?= $choice ?>]"/>
                            <?= $choice ?>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <button>Фильтровать</button>
    </form>
</div>
