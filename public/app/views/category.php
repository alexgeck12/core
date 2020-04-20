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
        <p class="menu-label">Товары</p>

        <div class="products">
            <?php foreach ($products as $product) { ?>
                <div class="product-card">
                    <h5 class="title is-6"><?php echo $product->name?></h5>
                    <img src="<?php echo $product->picture?>">
                    <p class="title is-7"><?php echo $product->annotation?></p>
                    <div>
                        <button type="button" data-product_id="<?php echo $product->id?>" class="button is-fullwidth is-dark is-uppercase add-to-cart">
                            В корзину
                        </button>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</section>