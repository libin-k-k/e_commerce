export default function ProductStickyActions({ inStock, onAddToCart, onBuyNow }) {
    return (
        <div className="pdp-sticky" role="region" aria-label="Purchase actions">
            <button
                type="button"
                className="btn btn--ghost pdp-sticky__btn"
                disabled={!inStock}
                onClick={onAddToCart}
            >
                Add to cart
            </button>
            <button
                type="button"
                className="btn btn--primary pdp-sticky__btn"
                disabled={!inStock}
                onClick={onBuyNow}
            >
                Buy now
            </button>
        </div>
    );
}
