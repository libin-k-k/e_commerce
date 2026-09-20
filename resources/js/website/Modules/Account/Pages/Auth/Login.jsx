import { Head, Link, useForm, usePage } from '@inertiajs/react';
import StorefrontLayout from '../../../../Shared/Layouts/StorefrontLayout';

export default function Login({ seo }) {
    const { appName } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({
        login: '',
        password: '',
        remember: true,
    });

    const onSubmit = (event) => {
        event.preventDefault();
        post('/login');
    };

    return (
        <StorefrontLayout seo={seo} current="account">
            <Head title={seo?.title} />
            <section className="auth-page">
                <div className="auth-page__card">
                    <p className="auth-page__brand">{appName}</p>
                    <h1 className="auth-page__title">Sign in</h1>
                    <p className="auth-page__text">
                        Use your email or mobile number. Guest cart and wishlist sync after login.
                    </p>
                    <form className="auth-page__form" onSubmit={onSubmit}>
                        <label className="auth-page__label" htmlFor="login-identifier">
                            Email or mobile
                            <input
                                id="login-identifier"
                                className="auth-page__input"
                                type="text"
                                value={data.login}
                                onChange={(event) => setData('login', event.target.value)}
                                required
                                autoComplete="username"
                                inputMode="email"
                                placeholder="email@mail.com or 9876543210"
                            />
                        </label>
                        {errors.login ? <p className="auth-page__error">{errors.login}</p> : null}
                        <label className="auth-page__label" htmlFor="login-password">
                            Password
                            <input
                                id="login-password"
                                className="auth-page__input"
                                type="password"
                                value={data.password}
                                onChange={(event) => setData('password', event.target.value)}
                                required
                                autoComplete="current-password"
                            />
                        </label>
                        {errors.password ? <p className="auth-page__error">{errors.password}</p> : null}
                        <label className="auth-page__check">
                            <input
                                type="checkbox"
                                checked={data.remember}
                                onChange={(event) => setData('remember', event.target.checked)}
                            />
                            Remember me
                        </label>
                        <button type="submit" className="btn btn--primary btn--block" disabled={processing}>
                            {processing ? 'Signing in...' : 'Sign in'}
                        </button>
                    </form>
                    <p className="auth-page__footer">
                        New here? <Link href="/register">Create an account</Link>
                    </p>
                </div>
            </section>
        </StorefrontLayout>
    );
}
