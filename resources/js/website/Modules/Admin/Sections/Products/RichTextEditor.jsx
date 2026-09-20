import { useEffect, useRef } from 'react';

const HEADINGS = [
    { label: 'Paragraph', value: 'p' },
    { label: 'Heading 1', value: 'h1' },
    { label: 'Heading 2', value: 'h2' },
    { label: 'Heading 3', value: 'h3' },
    { label: 'Heading 4', value: 'h4' },
    { label: 'Heading 5', value: 'h5' },
    { label: 'Heading 6', value: 'h6' },
];

/**
 * Free HTML editor (no extra packages). Saves HTML for the product description.
 */
export default function RichTextEditor({ value = '', onChange, error = null }) {
    const ref = useRef(null);

    useEffect(() => {
        if (ref.current && ref.current.innerHTML !== value) {
            ref.current.innerHTML = value || '';
        }
    }, [value]);

    const emit = () => {
        onChange(ref.current?.innerHTML ?? '');
    };

    const run = (command, commandValue = null) => {
        ref.current?.focus();
        document.execCommand(command, false, commandValue);
        emit();
    };

    const applyBlock = (tag) => {
        ref.current?.focus();
        document.execCommand('formatBlock', false, tag);
        emit();
    };

    const applyLink = () => {
        const url = window.prompt('Enter link URL', 'https://');
        if (!url) {
            return;
        }
        run('createLink', url.trim());
    };

    return (
        <div className="rich-editor">
            <div className="rich-editor__toolbar" role="toolbar" aria-label="Description formatting">
                <label className="rich-editor__select-wrap">
                    <span className="visually-hidden">Block type</span>
                    <select
                        className="rich-editor__select"
                        defaultValue="p"
                        onChange={(event) => applyBlock(event.target.value)}
                    >
                        {HEADINGS.map((item) => (
                            <option key={item.value} value={item.value}>
                                {item.label}
                            </option>
                        ))}
                    </select>
                </label>

                <div className="rich-editor__group">
                    <button type="button" className="rich-editor__btn" title="Bold" onClick={() => run('bold')}>
                        <strong>B</strong>
                    </button>
                    <button type="button" className="rich-editor__btn" title="Italic" onClick={() => run('italic')}>
                        <em>I</em>
                    </button>
                    <button
                        type="button"
                        className="rich-editor__btn"
                        title="Underline"
                        onClick={() => run('underline')}
                    >
                        <span className="rich-editor__u">U</span>
                    </button>
                    <button
                        type="button"
                        className="rich-editor__btn"
                        title="Strikethrough"
                        onClick={() => run('strikeThrough')}
                    >
                        <span className="rich-editor__s">S</span>
                    </button>
                </div>

                <div className="rich-editor__group">
                    <button
                        type="button"
                        className="rich-editor__btn"
                        title="Bullet list"
                        onClick={() => run('insertUnorderedList')}
                    >
                        • List
                    </button>
                    <button
                        type="button"
                        className="rich-editor__btn"
                        title="Numbered list"
                        onClick={() => run('insertOrderedList')}
                    >
                        1. List
                    </button>
                    <button
                        type="button"
                        className="rich-editor__btn"
                        title="Quote"
                        onClick={() => applyBlock('blockquote')}
                    >
                        Quote
                    </button>
                </div>

                <div className="rich-editor__group">
                    <button type="button" className="rich-editor__btn" title="Insert link" onClick={applyLink}>
                        Link
                    </button>
                    <button
                        type="button"
                        className="rich-editor__btn"
                        title="Remove link"
                        onClick={() => run('unlink')}
                    >
                        Unlink
                    </button>
                    <button
                        type="button"
                        className="rich-editor__btn"
                        title="Horizontal rule"
                        onClick={() => run('insertHorizontalRule')}
                    >
                        HR
                    </button>
                    <button
                        type="button"
                        className="rich-editor__btn"
                        title="Clear formatting"
                        onClick={() => run('removeFormat')}
                    >
                        Clear
                    </button>
                </div>
            </div>

            <div
                ref={ref}
                className="rich-editor__surface"
                contentEditable
                role="textbox"
                aria-multiline="true"
                aria-label="Product description"
                suppressContentEditableWarning
                onInput={emit}
            />
            {error ? <span className="banner-form__error">{error}</span> : null}
        </div>
    );
}
