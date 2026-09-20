import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Index({ seo, products = [] }) {
    const destroy = (id) => {
        if (!window.confirm('Delete this product?')) {
            return;
        }
        router.delete(`/admin/products/${id}`);
    };

    const lowStockProducts = products.filter((product) => product.is_low_stock);
    const threshold = products[0]?.low_stock_threshold ?? 10;

    return (
        <AdminLayout title="Products" current="products">
            <Head title={seo?.title} />
            <div className="admin-toolbar">
                <p className="admin-toolbar__text">Manage catalog products, pricing, stock, and variants.</p>
                <Link href="/admin/products/create" className="btn btn--primary">
                    Create Product
                </Link>
            </div>

            {lowStockProducts.length > 0 ? (
                <div className="stock-alert stock-alert--admin" role="alert">
                    <p className="stock-alert__title">Low stock alert</p>
                    <p className="stock-alert__text">
                        {lowStockProducts.length} product{lowStockProducts.length === 1 ? '' : 's'} at{' '}
                        {threshold} or fewer units:{' '}
                        {lowStockProducts.map((product) => product.name).join(', ')}.
                    </p>
                </div>
            ) : null}

            {products.length === 0 ? (
                <p className="admin-empty">No products yet. Create your first product.</p>
            ) : (
                <>
                    <div className="admin-card-list" aria-label="Products">
                        {products.map((product) => (
                            <article key={product.id} className="admin-card">
                                <div className="admin-banner-cell">
                                    {product.main_image_url ? (
                                        <img
                                            className="admin-banner-cell__thumb admin-product-thumb"
                                            src={product.main_image_url}
                                            alt=""
                                        />
                                    ) : (
                                        <span className="admin-banner-cell__thumb is-empty" />
                                    )}
                                    <div>
                                        <p className="admin-banner-cell__title">{product.name}</p>
                                        <p className="admin-banner-cell__sub">
                                            {product.sku} · {product.category_name}
                                            {product.is_unlaunched ? ' · Unlaunched' : ''}
                                        </p>
                                    </div>
                                </div>
                                <dl className="admin-card__meta">
                                    <div>
                                        <dt>Price</dt>
                                        <dd>₹{Number(product.price).toFixed(2)}</dd>
                                    </div>
                                    <div>
                                        <dt>Sale</dt>
                                        <dd>
                                            {product.sale_price != null
                                                ? `₹${Number(product.sale_price).toFixed(2)}`
                                                : '-'}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt>Stock</dt>
                                        <dd className={product.is_low_stock ? 'is-low-stock' : undefined}>
                                            {product.stock}
                                            {product.is_low_stock ? ' · Low' : ''}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt>Rating</dt>
                                        <dd>{product.rating}</dd>
                                    </div>
                                </dl>
                                <div className="admin-card__actions">
                                    <Link
                                        href={`/admin/products/${product.id}/edit`}
                                        className="btn btn--ghost btn--compact"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        className="btn btn--ghost btn--compact is-danger"
                                        onClick={() => destroy(product.id)}
                                    >
                                        Delete
                                    </button>
                                </div>
                            </article>
                        ))}
                    </div>

                    <div className="admin-table-wrap">
                        <table className="admin-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {products.map((product) => (
                                    <tr key={product.id}>
                                        <td>
                                            <div className="admin-banner-cell">
                                                {product.main_image_url ? (
                                                    <img
                                                        className="admin-banner-cell__thumb admin-product-thumb"
                                                        src={product.main_image_url}
                                                        alt=""
                                                    />
                                                ) : (
                                                    <span className="admin-banner-cell__thumb is-empty" />
                                                )}
                                                <div>
                                                    <p className="admin-banner-cell__title">{product.name}</p>
                                                    <p className="admin-banner-cell__sub">
                                                        {product.short_description}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{product.sku}</td>
                                        <td>
                                            {product.category_name}
                                            {product.subcategory_name
                                                ? ` / ${product.subcategory_name}`
                                                : ''}
                                        </td>
                                        <td>
                                            ₹{Number(product.price).toFixed(2)}
                                            {product.sale_price != null
                                                ? ` / ₹${Number(product.sale_price).toFixed(2)}`
                                                : ''}
                                        </td>
                                        <td>
                                            <span className={product.is_low_stock ? 'is-low-stock' : undefined}>
                                                {product.stock}
                                            </span>
                                            {product.is_low_stock ? (
                                                <span className="account-badge account-badge--warning">Low stock</span>
                                            ) : null}
                                        </td>
                                        <td>
                                            <span
                                                className={`account-badge account-badge--${product.is_unlaunched ? 'muted' : 'success'}`}
                                            >
                                                {product.is_unlaunched ? 'Unlaunched' : 'Live'}
                                            </span>
                                        </td>
                                        <td>
                                            <div className="admin-table__actions">
                                                <Link
                                                    href={`/admin/products/${product.id}/edit`}
                                                    className="admin-table__link"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    type="button"
                                                    className="admin-table__link is-danger"
                                                    onClick={() => destroy(product.id)}
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </>
            )}
        </AdminLayout>
    );
}
