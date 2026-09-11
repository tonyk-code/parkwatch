import json
import cv2
from detector import CarDetector
from roi import load_spots, spot_states
from pathlib import Path
import argparse


def open_source(source: str):
    src = int(source) if source.isdigit() else source
    cap = cv2.VideoCapture(src)

    if not cap.isOpened():
        return RuntimeError(f"Could not open video source: {source}")

    return cap


def main():
    args = argparse()
    spots_config = json.loads(Path(args.spots).read_text())
    spots = load_spots(spots_config["spots"])

    detector = CarDetector(conf_threshold=args.conf)
    cap = open_source(args.source)

    last_state = {}
    frame_idx = 0

    while True:
        ok, frame = cap.read()
        if not ok:
            break

        car_boxes = detector.detect_cars(frame)
        state = spot_states(spots, car_boxes)

        changed = {sid: occ for sid, occ in state.items() if last_state.get(sid) != occ}
        for spot_id, occupied in changed.items():
            status = "OCCUPIED" if occupied else "FREE"
            print(f"[frame {frame_idx}] spot {spot_id} -> {status}")
        last_state = state
        frame_idx += 1
