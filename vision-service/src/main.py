"""Vision service entry point -- this is Phase 1/2 of the roadmap.

For each frame it:
  1. detects cars with the pretrained YOLOv8 model
  2. checks which parking-spot regions are covered by a car
  3. prints spot state changes -- this is where the Supabase write goes
     once Phase 3 (persist state) is wired up

Usage:
    python main.py --source path/to/video.mp4 --show   # watch it live
    python main.py --source 0 --show                    # webcam, live
    python main.py --source clip.mp4 --save-annotated preview.jpg

Press 'q' in the preview window to stop early.
"""

import argparse
import json
from pathlib import Path

import cv2

from detector import CarDetector
from roi import load_spots, spot_states
import numpy as np

MAX_DISPLAY_WIDTH = 1280
MAX_DISPLAY_HEIGHT = 720

def fit_to_screen(image):
    h, w = image.shape[:2]
    scale = min(MAX_DISPLAY_WIDTH / w, MAX_DISPLAY_HEIGHT / h, 1.0)
    if scale >= 1.0:
        return image
    return cv2.resize(image, (int(w * scale), int(h * scale)))


def parse_args():
    p = argparse.ArgumentParser()
    p.add_argument("--source", default="0", help="Video file path, or camera index (e.g. 0)")
    p.add_argument("--spots", default="config/spots.json", help="Path to spot region config")
    p.add_argument("--conf", type=float, default=0.25, help="Detection confidence threshold")
    p.add_argument("--save-annotated", default=None, help="Optional: save one annotated frame here")
    p.add_argument("--show", action="store_true", help="Open a live preview window (needs a display)")
    return p.parse_args()


def open_source(source: str):
    src = int(source) if source.isdigit() else source  # "0" -> webcam index 0
    cap = cv2.VideoCapture(src)
    if not cap.isOpened():
        raise RuntimeError(f"Could not open video source: {source}")
    return cap


def draw_preview(frame, spots, state, car_boxes=()):
    preview = frame.copy()

    for (x1, y1, x2, y2) in car_boxes:
        cv2.rectangle(preview, (x1, y1), (x2, y2), (255, 0, 0), 1)

    for spot in spots:
        pts = np.array(spot.polygon, dtype=np.int32)
        color = (0, 0, 255) if state[spot.id] else (0, 200, 0)  # BGR: red / green
        cv2.polylines(preview, [pts], isClosed=True, color=color, thickness=2)
        cv2.putText(preview, spot.id, tuple(pts[0]), cv2.FONT_HERSHEY_SIMPLEX, 0.5, color, 2)
    return preview


def main():
    args = parse_args()
    spots_config = json.loads(Path(args.spots).read_text())
    spots = load_spots(spots_config["spots"])

    detector = CarDetector(conf_threshold=args.conf)
    cap = open_source(args.source)

    last_state = {}
    frame_idx = 0
    saved_preview = False

    while True:
        ok, frame = cap.read()
        if not ok:
            break

        car_boxes = detector.detect_cars(frame)
        state = spot_states(spots, car_boxes)

        # only act when something actually changed -- this is the line that
        # becomes a Supabase write in Phase 3
        changed = {sid: occ for sid, occ in state.items() if last_state.get(sid) != occ}
        for spot_id, occupied in changed.items():
            status = "OCCUPIED" if occupied else "FREE"
            print(f"[frame {frame_idx}] spot {spot_id} -> {status}")
            # TODO (Phase 3): replace the print above with, e.g.
            # supabase.table("occupancy_state").update({
            #     "occupied": occupied, "updated_at": "now()"
            # }).eq("spot_id", spot_id).execute()

        if args.save_annotated and not saved_preview and any(state.values()):
            cv2.imwrite(args.save_annotated, draw_preview(frame, spots, state, car_boxes))
            saved_preview = True

        if args.show:
            preview = draw_preview(frame, spots, state, car_boxes)
            cv2.imshow("ParkWatch", fit_to_screen(preview))
            # waitKey also pumps the window's event loop -- skip it and the
            # window just won't update. 1ms is enough; it doesn't slow
            # things down noticeably, since model inference is the real
            # bottleneck per frame anyway.
            if cv2.waitKey(1) & 0xFF == ord("q"):
                print("Stopped early by user (q).")
                break

        last_state = state
        frame_idx += 1

    cap.release()
    if args.show:
        cv2.destroyAllWindows()
    print(f"Done. Processed {frame_idx} frames.")


if __name__ == "__main__":
    main()
