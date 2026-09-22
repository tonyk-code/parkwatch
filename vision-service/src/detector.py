from ultralytics import YOLO

COCO_CAR_CLASS_ID = 2


class CarDetector:
    def __init__(self, model_path: str = "yolov8n.pt", conf_threshold: float = 0.25):
        self.model = YOLO(model_path)
        self.conf_threshold = conf_threshold

    def detect_cars(self, frame) -> list:
        results = self.model(
            frame,
            classes=[COCO_CAR_CLASS_ID],
            conf=self.conf_threshold,
            verbose=False,
        )
        boxes = results[0].boxes
        return [tuple(map(int, b)) for b in boxes.xyxy.tolist()]
