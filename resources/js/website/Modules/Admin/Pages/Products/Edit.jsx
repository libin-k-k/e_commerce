import { Head } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import ProductForm from '../../Sections/Products/ProductForm';

export default function Edit({ seo, product, options }) {
    return (
        <AdminLayout title="Edit Product" current="products">
            <Head title={seo?.title} />
            <ProductForm
                options={options}
                product={product}
                submitUrl={`/admin/products/${product.id}`}
                method="put"
                submitLabel="Update Product"
            />
        </AdminLayout>
    );
}
