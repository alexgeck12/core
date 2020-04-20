<section class="main-content columns is-fullheight">
    <aside class="column is-2 is-narrow-mobile is-fullheight section is-hidden-mobile">
        <p class="menu-label is-hidden-touch">Категории</p>
        <ul class="menu-list">
            <li>
                <?php foreach ($categories as $category) { ?>
                <a href="/categories/get/?category_id=<?php echo $category->id?>" class="is-size-7">
                    <?php echo $category->name?>
                </a>
                <?php } ?>
            </li>
        </ul>
    </aside>

    <div class="container column is-10 section">
        <p class="menu-label">Выберите категорию</p>
    </div>
</section>