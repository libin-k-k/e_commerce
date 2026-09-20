import { Head } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import CategoryForm from '../../Sections/Categories/CategoryForm';

export default function Edit({ seo, category, options }) {
    return (
        <AdminLayout title="Edit Category" current="categories">
            <Head title={seo?.title} />
            <CategoryForm
                options={options}
                category={category}
                submitUrl={`/admin/categories/${category.id}`}
                method="put"
                submitLabel="Update Category"
            />
        </AdminLayout>
    );
}
