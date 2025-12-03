CREATE TABLE product_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_produk INT NOT NULL,
    id_user INT NOT NULL,
    rating_value INT NOT NULL CHECK (rating_value >= 1 AND rating_value <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_produk) REFERENCES produk(id_produk) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);