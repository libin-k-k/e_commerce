import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Index({ seo, categories = [] }) {
    const destroy = (id, hasChildren) => {
        if (hasChildren) {
            window.alert('Delete or reassign sub categories first.');
            return;
        }

        if (!window.confirm('Delete this category?')) {
            return;
        }

        router.delete(`/admin/categories/${id}`);
    };

    return (
        <AdminLayout title="Categories" current="categories">
            <Head title={seo?.title} />
            <div className="admin-toolbar">
                <p className="admin-toolbar__text">
                    Manage main categories and their sub categories with images.
                </p>
                <Link href="/admin/categories/create" className="btn btn--primary">
                    Create Category
                </Link>
            </div>

            {categories.length === 0 ? (
                <p className="admin-empty">No categories yet. Create your first main category.</p>
            ) : (
                <div className="admin-category-tree">
                    {categories.map((category) => (
                        <article key={category.id} className="admin-category-group">
                            <div className="admin-category-row">
                                {category.image_url ? (
                                    <img
                                        className="admin-category-row__thumb"
                                        src={category.image_url}
                                        alt=""
                                    />
                                ) : (
                                    <span className="admin-category-row__thumb is-empty" />
                                )}
                                <div className="admin-category-row__body">
                                    <p className="admin-category-row__title">{category.name}</p>
                                    <p className="admin-category-row__meta">
                                        Main · /{category.slug} · {category.children?.length ?? 0}{' '}
                                        sub
                                        {!category.is_active ? ' · Inactive' : ''}
                                    </p>
                                </div>
                                <div className="admin-category-row__actions">
                                    <Link
                                        href={`/admin/categories/create?parent=${category.id}`}
                                        className="admin-table__link"
                                    >
                                        Add sub
                                    </Link>
                                    <Link
                                        href={`/admin/categories/${category.id}/edit`}
                                        className="admin-table__link"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        className="admin-table__link is-danger"
                                        onClick={() =>
                                            destroy(category.id, (category.children?.length ?? 0) > 0)
                                        }
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>

                            {(category.children ?? []).length > 0 ? (
                                <ul className="admin-category-subs">
                                    {category.children.map((child) => (
                                        <li key={child.id} className="admin-category-row is-child">
                                            {child.image_url ? (
                                                <img
                                                    className="admin-category-row__thumb"
                                                    src={child.image_url}
                                                    alt=""
                                                />
                                            ) : (
                                                <span className="admin-category-row__thumb is-empty" />
                                            )}
                                            <div className="admin-category-row__body">
                                                <p className="admin-category-row__title">{child.name}</p>
                                                <p className="admin-category-row__meta">
                                                    Sub · /{child.slug}
                                                    {!child.is_active ? ' · Inactive' : ''}
                                                </p>
                                            </div>
                                            <div className="admin-category-row__actions">
                                                <Link
                                                    href={`/admin/categories/${child.id}/edit`}
                                                    className="admin-table__link"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    type="button"
                                                    className="admin-table__link is-danger"
                                                    onClick={() => destroy(child.id, false)}
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                            ) : null}
                        </article>
                    ))}
                </div>
            )}
        </AdminLayout>
    );
}
