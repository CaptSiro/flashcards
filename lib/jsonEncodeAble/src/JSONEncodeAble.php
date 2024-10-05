<?php



class JSONEncodeAble implements JsonSerializable {
    public function jsonSerialize(): object {
        $serialized = [];
        foreach ($this as $key => $value) {
            if (isset($this->$key)) {
                $serialized[$key] = $value;
            }
        }

        return ((object)$serialized);
    }
}