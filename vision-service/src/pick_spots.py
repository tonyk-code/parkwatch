"""Click-to-define your parking spot regions on one real frame -- as
4-point polygons, so angled/parallelogram-shaped spots work, not just
axis-aligned rectangles.

The preview window is capped to a fixed max size (1280x720) regardless of
your video's actual resolution, and stays non-resizable -- so a click's
on-screen position always maps to a known, exact spot in the original
frame. Coordinates are always saved at the video's real, full resolution,
never the shrunk display size.

Usage:
    python pick_spots.py --source path/to/video.mp4

For each spot: click its 4 corners, in any order you like -- they're
sorted into a proper polygon automatically, so you don't have to click
carefully "around" the shape.
Press 's' to save to config/spots.json, 'u' to undo the last spot, 'q' to quit.
"""

import argparse
import json

import cv2
import numpy as np

MAX_DISPLAY_WIDTH = 1280
MAX_DISPLAY_HEIGHT = 720

clicks = []          # always stored in ORIGINAL frame coordinates
spots = []            # polygons always stored in ORIGINAL frame coordinates
display_frame = None
scale = 1.0            # display_pixels = original_pixels * scale


def to_original(x, y):
    return x / scale, y / scale


def to_display(x, y):
    return int(round(x * scale)), int(round(y * scale))


def order_corners(points):
    """Sorts 4 clicked points into consistent (clockwise) order around
    their center. Without this, clicking corners out of sequence would
    produce a self-intersecting "bowtie" shape instead of a clean quad."""
    pts = np.array(points, dtype=np.float32)
    center = pts.mean(axis=0)
    angles = np.arctan2(pts[:, 1] - center[1], pts[:, 0] - center[0])
    order = np.argsort(angles)
    return pts[order].tolist()


def on_mouse(event, x, y, flags, param):
    if event != cv2.EVENT_LBUTTONDOWN:
        return
    clicks.append(to_original(x, y))  # convert the instant we receive it
    if len(clicks) == 4:
        spot_id = f"A{len(spots) + 1}"
        polygon = [[int(round(px)), int(round(py))] for px, py in order_corners(clicks)]
        spots.append({"id": spot_id, "polygon": polygon})
        clicks.clear()
    redraw()


def redraw():
    preview = display_frame.copy()
    for spot in spots:
        pts = np.array([to_display(px, py) for px, py in spot["polygon"]], dtype=np.int32)
        cv2.polylines(preview, [pts], isClosed=True, color=(0, 200, 0), thickness=2)
        cv2.putText(preview, spot["id"], tuple(pts[0]), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 200, 0), 2)
    for px, py in clicks:  # show in-progress clicks before the 4th lands
        dx, dy = to_display(px, py)
        cv2.circle(preview, (dx, dy), 4, (0, 0, 255), -1)
    cv2.imshow("pick spots", preview)


def main():
    global display_frame, scale
    p = argparse.ArgumentParser()
    p.add_argument("--source", default="0")
    p.add_argument("--out", default="config/spots.json")
    args = p.parse_args()

    src = int(args.source) if args.source.isdigit() else args.source
    cap = cv2.VideoCapture(src)
    ok, frame = cap.read()
    cap.release()
    if not ok:
        raise RuntimeError(f"Could not read a frame from: {args.source}")

    h, w = frame.shape[:2]
    scale = min(MAX_DISPLAY_WIDTH / w, MAX_DISPLAY_HEIGHT / h, 1.0)  # never upscale
    display_frame = cv2.resize(frame, (int(w * scale), int(h * scale))) if scale < 1.0 else frame.copy()

    # no cv2.WINDOW_NORMAL here on purpose -- default autosize means the
    # window can't be dragged to a different size, so display pixels always
    # exactly match display_frame's pixels, with no extra scaling drift.
    cv2.imshow("pick spots", display_frame)
    cv2.setMouseCallback("pick spots", on_mouse)
    print(f"Video frame is {w}x{h}, showing at {display_frame.shape[1]}x{display_frame.shape[0]} "
          f"(scale {scale:.2f}). Saved coordinates are always full-resolution.")
    print("Click 4 corners per spot, any order. 's' save, 'u' undo, 'q' quit.")

    while True:
        key = cv2.waitKey(20) & 0xFF
        if key == ord("s"):
            with open(args.out, "w") as f:
                json.dump({"spots": spots}, f, indent=2)
            print(f"Saved {len(spots)} spots to {args.out}")
        elif key == ord("u") and spots:
            spots.pop()
            redraw()
        elif key == ord("q"):
            break

    cv2.destroyAllWindows()


if __name__ == "__main__":
    main()