from dataclasses import dataclass


@dataclass
class Spot:
    id: str
    box: tuple


def load_spots(spot_config: list) -> list:
    return [Spot(id=s["id"], box=tuple(s["box"])) for s in spot_config]


def intersection_area(a, b) -> float:
    ax1, ay1, ax2, ay2 = a
    bx1, by1, bx2, by2 = b
    ix1, iy1 = max(ax1, bx1), max(ay1, by1)
    ix2, iy2 = min(ax2, bx2), min(ay2, by2)

    if ix2 <= ix1 or iy2 <= iy1:
        return 0.0
    return (ix2 - ix1) * (iy2 - iy1)


def overlap_ratio(spot_box, car_box) -> float:
    spot_area = (spot_box[2] - spot_box[0]) * (spot_box[3] - spot_box[1])

    if spot_area <= 0:
        return 0.0

    return intersection_area(spot_box, car_box) / spot_area


def spot_states(spots: list, car_boxes: list, threshold: float = 0.4) -> dict:
    states = {}
    for spot in spots:
        occupied = any(
            overlap_ratio(spot.box, car_box) >= threshold for car_box in car_boxes
        )
        states[spot.id] = occupied
    return states
