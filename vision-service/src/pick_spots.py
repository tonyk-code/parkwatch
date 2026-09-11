"""Click-to-define your parking spot regions on one real frame, instead of
guessing pixel coordinates by hand.

Usage:
    python pick_spots.py --source path/to/video.mp4

For each spot: click the top-left corner, then the bottom-right corner.
Press 's' to save to config/spots.json, 'u' to undo the last spot, 'q' to quit.
"""

import argparse
import json

import cv2

clicks = []
spots = []
frame = None


def on_mouse(event, x, y, flags, param):
    if event != cv2.EVENT_LBUTTONDOWN:
        return
    clicks.append((x, y))
    if len(clicks) == 2:
        x1, y1 = clicks[0]
        x2, y2 = clicks[1]
        spot_id = f"A{len(spots) + 1}"
        spots.append({"id": spot_id, "box": [min(x1, x2), min(y1, y2), max(x1, x2), max(y1, y2)]})
        clicks.clear()
        redraw()


def redraw():
    preview = frame.copy()
    for spot in spots:
        x1, y1, x2, y2 = spot["box"]
        cv2.rectangle(preview, (x1, y1), (x2, y2), (0, 200, 0), 2)
        cv2.putText(preview, spot["id"], (x1, y1 - 6), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 200, 0), 2)
    cv2.imshow("pick spots", preview)


def main():
    global frame
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

    cv2.imshow("pick spots", frame)
    cv2.setMouseCallback("pick spots", on_mouse)
    print("Click top-left then bottom-right for each spot. 's' save, 'u' undo, 'q' quit.")

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
