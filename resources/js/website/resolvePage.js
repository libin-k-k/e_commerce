export async function resolvePageComponent(name, pages) {
    const path = `./Modules/${name}.jsx`;
    const importer = pages[path];

    if (!importer) {
        throw new Error(`Inertia page not found: ${path}`);
    }

    const module = typeof importer === 'function' ? await importer() : importer;

    return module.default ?? module;
}
