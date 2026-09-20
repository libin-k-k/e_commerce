import { useEffect, useId, useRef, useState } from 'react';

export default function HelpChatSection({ chat }) {
    const listId = useId();
    const listRef = useRef(null);
    const [draft, setDraft] = useState('');
    const [messages, setMessages] = useState([
        {
            id: 'welcome',
            role: 'assistant',
            text: chat.welcome,
        },
    ]);

    useEffect(() => {
        const node = listRef.current;
        if (node) {
            node.scrollTop = node.scrollHeight;
        }
    }, [messages]);

    const pushAssistant = (text) => {
        setMessages((current) => [
            ...current,
            {
                id: `a-${Date.now()}`,
                role: 'assistant',
                text,
            },
        ]);
    };

    const sendText = (raw) => {
        const text = raw.trim();
        if (!text) {
            return;
        }

        setMessages((current) => [
            ...current,
            {
                id: `u-${Date.now()}`,
                role: 'user',
                text,
            },
        ]);
        setDraft('');

        const key = matchReplyKey(text, chat.quickReplies);
        const reply = chat.replies[key] ?? chat.replies.default;

        window.setTimeout(() => pushAssistant(reply), 350);
    };

    const onSubmit = (event) => {
        event.preventDefault();
        sendText(draft);
    };

    return (
        <section className="help-chat" aria-labelledby={listId}>
            <header className="help-chat__header">
                <div className="help-chat__avatar" aria-hidden="true">
                    ?
                </div>
                <div className="help-chat__meta">
                    <h1 id={listId} className="help-chat__title">
                        Support chat
                    </h1>
                    <p className="help-chat__status">
                        <span className="help-chat__status-dot" aria-hidden="true" />
                        Online now
                    </p>
                </div>
            </header>

            <div className="help-chat__thread" ref={listRef} role="log" aria-live="polite">
                {messages.map((message) => (
                    <div
                        key={message.id}
                        className={`help-chat__bubble help-chat__bubble--${message.role}`}
                    >
                        <p>{message.text}</p>
                    </div>
                ))}

                {chat.quickReplies?.length ? (
                    <div className="help-chat__suggestions" aria-label="Suggested topics">
                        {chat.quickReplies.map((item) => (
                            <button
                                key={item.id}
                                type="button"
                                className="help-chat__chip"
                                onClick={() => sendText(item.label)}
                            >
                                {item.label}
                            </button>
                        ))}
                    </div>
                ) : null}
            </div>

            <form className="help-chat__composer" onSubmit={onSubmit}>
                <label className="visually-hidden" htmlFor="help-chat-input">
                    Type a message
                </label>
                <input
                    id="help-chat-input"
                    className="help-chat__input"
                    type="text"
                    value={draft}
                    onChange={(event) => setDraft(event.target.value)}
                    placeholder="Type your message..."
                    autoComplete="off"
                />
                <button type="submit" className="btn btn--primary help-chat__send" disabled={!draft.trim()}>
                    Send
                </button>
            </form>
        </section>
    );
}

function matchReplyKey(text, quickReplies = []) {
    const normalized = text.toLowerCase();

    for (const item of quickReplies) {
        if (normalized.includes(item.id) || normalized.includes(item.label.toLowerCase())) {
            return item.id;
        }
    }

    if (normalized.includes('order') || normalized.includes('track')) {
        return 'orders';
    }
    if (normalized.includes('deliver') || normalized.includes('ship')) {
        return 'delivery';
    }
    if (normalized.includes('return') || normalized.includes('refund')) {
        return 'returns';
    }
    if (normalized.includes('pay') || normalized.includes('upi') || normalized.includes('card')) {
        return 'payments';
    }

    return 'default';
}
