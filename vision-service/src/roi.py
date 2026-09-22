from dataclasses import dataclass

import cv2
import numpy as np


@dataclass
class Spot:
    id: str
    polygon: list  


def load_spots(spots_config: list) -> list:
    return [Spot(id=s["id"], polygon=s["polygon"]) for s in spots_config]


def _box_to_polygon(box):
    x1, y1, x2, y2 = box
    return [[x1, y1], [x2, y1], [x2, y2], [x1, y2]]


def overlap_ratio(spot_polygon, car_box) -> float:
    spot_pts = np.array(spot_polygon, dtype=np.float32)
    car_pts = np.array(_box_to_polygon(car_box), dtype=np.float32)

    spot_area = cv2.contourArea(spot_pts)
    if spot_area <= 0:
        return 0.0

    intersection_area, _ = cv2.intersectConvexConvex(spot_pts, car_pts)
    return intersection_area / spot_area


def spot_states(spots: list, car_boxes: list, threshold: float = 0.4) -> dict:
    states = {}
    for spot in spots:
        occupied = any(overlap_ratio(spot.polygon, car_box) >= threshold for car_box in car_boxes)
        states[spot.id] = occupied
    return states


if __name__ == "__main__":
    spots = load_spots([{"id": "A1", "polygon": [[280, 330], [490, 330], [490, 430], [280, 430]]}])
    car_box = (284, 332, 482, 428)
    print("overlap ratio:", overlap_ratio(spots[0].polygon, car_box))
    print("state:", spot_states(spots, [car_box]))