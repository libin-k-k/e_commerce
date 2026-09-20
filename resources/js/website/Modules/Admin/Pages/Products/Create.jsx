import { Head } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import ProductForm from '../../Sections/Products/ProductForm';

export default function Create({ seo, options }) {
    return (
        <AdminLayout title="Create Product" current="products">
            <Head title={seo?.title} />
            <ProductForm
                options={options}
                submitUrl="/admin/products"
                method="post"
                submitLabel="Create Product"
            />
        </AdminLayout>
    );
}
