import { useEffect, useRef, useState } from 'react';

/**
 * Select + crop an image to a fixed aspect ratio, then emit a File for upload.
 */
export default function ImageCropField({
    label,
    hint,
    aspect = 21 / 9,
    previewUrl = null,
    error = null,
    onChange,
    required = false,
}) {
    const inputRef = useRef(null);
    const stageRef = useRef(null);
    const dragRef = useRef(null);
    const [sourceUrl, setSourceUrl] = useState(null);
    const [natural, setNatural] = useState({ width: 0, height: 0 });
    const [crop, setCrop] = useState({ x: 0.5, y: 0.5, size: 0.78 });
    const [open, setOpen] = useState(false);
    const [localPreview, setLocalPreview] = useState(previewUrl);

    useEffect(() => {
        setLocalPreview(previewUrl);
    }, [previewUrl]);

    useEffect(() => {
        return () => {
            if (sourceUrl) {
                URL.revokeObjectURL(sourceUrl);
            }
        };
    }, [sourceUrl]);

    const openPicker = () => inputRef.current?.click();

    const onFile = (event) => {
        const file = event.target.files?.[0];
        event.target.value = '';
        if (!file) {
            return;
        }

        const url = URL.createObjectURL(file);
        const image = new Image();
        image.onload = () => {
            setNatural({ width: image.naturalWidth, height: image.naturalHeight });
            setSourceUrl(url);
            setCrop(clampCrop({ x: 0.5, y: 0.5, size: 0.78 }, aspect, {
                width: image.naturalWidth,
                height: image.naturalHeight,
            }));
            setOpen(true);
        };
        image.src = url;
    };

    const onPointerDown = (event) => {
        if (!open) {
            return;
        }
        const stage = stageRef.current;
        if (!stage) {
            return;
        }
        const rect = stage.getBoundingClientRect();
        const layout = containedImageLayout(aspect, natural);
        dragRef.current = {
            startX: event.clientX,
            startY: event.clientY,
            originX: crop.x,
            originY: crop.y,
            width: rect.width,
            height: rect.height,
            imgWidth: layout.width,
            imgHeight: layout.height,
        };
        event.currentTarget.setPointerCapture(event.pointerId);
    };

    const onPointerMove = (event) => {
        const drag = dragRef.current;
        if (!drag) {
            return;
        }
        const dx = (event.clientX - drag.startX) / drag.width / drag.imgWidth;
        const dy = (event.clientY - drag.startY) / drag.height / drag.imgHeight;
        setCrop((current) =>
            clampCrop(
                {
                    x: drag.originX + dx,
                    y: drag.originY + dy,
                    size: current.size,
                },
                aspect,
                natural,
            ),
        );
    };

    const onPointerUp = () => {
        dragRef.current = null;
    };

    const applyCrop = async () => {
        if (!sourceUrl || !natural.width) {
            return;
        }

        const image = await loadImage(sourceUrl);
        const box = cropBoxPixels(crop, aspect, natural);
        const canvas = document.createElement('canvas');
        const outputWidth = Math.min(1600, Math.max(640, Math.round(box.width)));
        const outputHeight = Math.round(outputWidth / aspect);
        canvas.width = outputWidth;
        canvas.height = outputHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(
            image,
            box.sx,
            box.sy,
            box.width,
            box.height,
            0,
            0,
            outputWidth,
            outputHeight,
        );

        const blob = await new Promise((resolve) => {
            canvas.toBlob((value) => resolve(value), 'image/jpeg', 0.92);
        });

        if (!blob) {
            return;
        }

        const file = new File([blob], `crop-${Date.now()}.jpg`, { type: 'image/jpeg' });
        const preview = URL.createObjectURL(blob);
        setLocalPreview(preview);
        onChange(file);
        setOpen(false);
        if (sourceUrl) {
            URL.revokeObjectURL(sourceUrl);
        }
        setSourceUrl(null);
    };

    const cancelCrop = () => {
        setOpen(false);
        if (sourceUrl) {
            URL.revokeObjectURL(sourceUrl);
        }
        setSourceUrl(null);
    };

    const box = cropBoxPercent(crop, aspect, natural);
    const stageAspectClass =
        aspect === 1 ? ' is-square' : aspect < 1 ? ' is-portrait' : ' is-landscape';

    return (
        <div className="image-crop-field">
            <div className="image-crop-field__head">
                <span className="banner-form__label">
                    {label}
                    {required ? ' *' : ''}
                </span>
                <span className="banner-form__hint">{hint}</span>
            </div>

            <div className="image-crop-field__preview-wrap">
                {localPreview ? (
                    <img
                        className={`image-crop-field__preview${aspect === 1 ? ' is-square' : ''}`}
                        src={localPreview}
                        alt=""
                    />
                ) : (
                    <div className="image-crop-field__empty">No image selected</div>
                )}
                <div className="image-crop-field__actions">
                    <button type="button" className="btn btn--ghost btn--compact" onClick={openPicker}>
                        {localPreview ? 'Change & crop' : 'Select & crop'}
                    </button>
                </div>
            </div>

            <input
                ref={inputRef}
                className="visually-hidden"
                type="file"
                accept="image/*"
                onChange={onFile}
            />
            {error ? <span className="banner-form__error">{error}</span> : null}

            {open && sourceUrl ? (
                <div className="image-crop-modal" role="dialog" aria-modal="true" aria-label={`Crop ${label}`}>
                    <div className="image-crop-modal__card">
                        <h3 className="image-crop-modal__title">Crop {label}</h3>
                        <p className="image-crop-modal__text">
                            Drag the frame to reposition. Use the slider to zoom. Target ratio{' '}
                            {aspectLabel(aspect)}.
                        </p>

                        <div
                            ref={stageRef}
                            className={`image-crop-modal__stage${stageAspectClass}`}
                            data-aspect={aspect}
                            onPointerDown={onPointerDown}
                            onPointerMove={onPointerMove}
                            onPointerUp={onPointerUp}
                            onPointerCancel={onPointerUp}
                        >
                            <img className="image-crop-modal__image" src={sourceUrl} alt="" draggable={false} />
                            <div
                                className="image-crop-modal__frame"
                                style={{
                                    left: `${box.left}%`,
                                    top: `${box.top}%`,
                                    width: `${box.width}%`,
                                    height: `${box.height}%`,
                                }}
                            />
                        </div>

                        <label className="image-crop-modal__zoom">
                            <span>Zoom</span>
                            <input
                                type="range"
                                min="0.35"
                                max="1"
                                step="0.01"
                                value={crop.size}
                                onChange={(event) =>
                                    setCrop((current) =>
                                        clampCrop(
                                            { ...current, size: Number(event.target.value) },
                                            aspect,
                                            natural,
                                        ),
                                    )
                                }
                            />
                        </label>

                        <div className="image-crop-modal__actions">
                            <button type="button" className="btn btn--primary" onClick={applyCrop}>
                                Apply crop
                            </button>
                            <button type="button" className="btn btn--ghost" onClick={cancelCrop}>
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            ) : null}
        </div>
    );
}

function aspectLabel(aspect) {
    if (Math.abs(aspect - 1) < 0.01) {
        return '1:1';
    }
    if (Math.abs(aspect - 21 / 9) < 0.01) {
        return '21:9';
    }
    if (Math.abs(aspect - 4 / 5) < 0.01) {
        return '4:5';
    }

    return aspect.toFixed(2);
}

function loadImage(url) {
    return new Promise((resolve, reject) => {
        const image = new Image();
        image.onload = () => resolve(image);
        image.onerror = reject;
        image.src = url;
    });
}

/**
 * Where the contained image sits inside a stage that uses the crop aspect ratio.
 * Values are fractions of the stage (0-1).
 */
function containedImageLayout(stageAspect, natural) {
    if (!natural.width || !natural.height) {
        return { left: 0, top: 0, width: 1, height: 1 };
    }

    const imageAspect = natural.width / natural.height;

    if (imageAspect > stageAspect) {
        const height = stageAspect / imageAspect;

        return { left: 0, top: (1 - height) / 2, width: 1, height };
    }

    const width = imageAspect / stageAspect;

    return { left: (1 - width) / 2, top: 0, width, height: 1 };
}

function clampCrop(crop, aspect, natural) {
    const size = Math.min(1, Math.max(0.35, crop.size));
    const { widthRatio, heightRatio } = frameRatios(aspect, natural, size);
    const x = Math.min(1 - widthRatio / 2, Math.max(widthRatio / 2, crop.x));
    const y = Math.min(1 - heightRatio / 2, Math.max(heightRatio / 2, crop.y));

    return { x, y, size };
}

/**
 * Crop window as fractions of the source image (not the stage).
 */
function frameRatios(aspect, natural, size) {
    if (!natural.width || !natural.height) {
        return { widthRatio: size, heightRatio: size / aspect };
    }

    const imageAspect = natural.width / natural.height;

    // size = fraction of the limiting image axis that the crop covers
    if (imageAspect > aspect) {
        const heightRatio = size;
        const widthRatio = (heightRatio * aspect) / imageAspect;

        return { widthRatio, heightRatio };
    }

    const widthRatio = size;
    const heightRatio = (widthRatio * imageAspect) / aspect;

    return { widthRatio, heightRatio };
}

/**
 * Map image-space crop window onto stage percentages (object-fit: contain).
 */
function cropBoxPercent(crop, aspect, natural) {
    const { widthRatio, heightRatio } = frameRatios(aspect, natural, crop.size);
    const layout = containedImageLayout(aspect, natural);

    return {
        left: (layout.left + (crop.x - widthRatio / 2) * layout.width) * 100,
        top: (layout.top + (crop.y - heightRatio / 2) * layout.height) * 100,
        width: widthRatio * layout.width * 100,
        height: heightRatio * layout.height * 100,
    };
}

function cropBoxPixels(crop, aspect, natural) {
    const { widthRatio, heightRatio } = frameRatios(aspect, natural, crop.size);
    const width = natural.width * widthRatio;
    const height = natural.height * heightRatio;
    const sx = natural.width * (crop.x - widthRatio / 2);
    const sy = natural.height * (crop.y - heightRatio / 2);

    return {
        sx: Math.max(0, sx),
        sy: Math.max(0, sy),
        width: Math.min(natural.width, width),
        height: Math.min(natural.height, height),
    };
}
