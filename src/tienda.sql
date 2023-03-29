CREATE EXTENSION IF NOT EXISTS pgcrypto;
CREATE EXTENSION IF NOT EXISTS unaccent;

DROP TABLE IF EXISTS articulos CASCADE;

CREATE TABLE articulos (
    id          bigserial     PRIMARY KEY,
    codigo      varchar(13)   NOT NULL UNIQUE,
    descripcion varchar(255)  NOT NULL,
    precio      numeric(7, 2) NOT NULL,
    stock       int           NOT NULL,
    id_categoria bigint       NOT NULL REFERENCES categorias (id)
);

DROP TABLE IF EXISTS categorias CASCADE;

CREATE TABLE categorias (
    id          bigserial PRIMARY KEY,
    categoria   varchar(255) UNIQUE NOT NULL
);

DROP TABLE IF EXISTS usuarios CASCADE;

CREATE TABLE usuarios (
    id       bigserial    PRIMARY KEY,
    usuario  varchar(255) NOT NULL UNIQUE,
    password varchar(255) NOT NULL,
    validado   boolean      NOT NULL
);

DROP TABLE IF EXISTS facturas CASCADE;

CREATE TABLE facturas (
    id         bigserial  PRIMARY KEY,
    created_at timestamp  NOT NULL DEFAULT localtimestamp(0),
    usuario_id bigint NOT NULL REFERENCES usuarios (id)
);

DROP TABLE IF EXISTS articulos_facturas CASCADE;

CREATE TABLE articulos_facturas (
    articulo_id bigint NOT NULL REFERENCES articulos (id),
    factura_id  bigint NOT NULL REFERENCES facturas (id),
    cantidad    int    NOT NULL,
    PRIMARY KEY (articulo_id, factura_id)
);

-- Carga inicial de datos de prueba:

INSERT INTO articulos (codigo, descripcion, precio, stock, id_categoria)
    VALUES ('18273892389', 'Yogur piña', 2.50, 20, 2),
           ('83745828273', 'Tigretón', 1.10, 30, 2),
           ('51736128495', 'Disco duro SSD 500 GB', 150.30, 15, 1),
           ('51786128435', 'Disco duro M2 500 GB', 180.30, 0, 1),
           ('83745228673', 'Chandal', 30.10, 15, 3),
           ('51786198495', 'Traje', 250.30, 1, 3);

INSERT INTO usuarios (usuario, password, validado)
    VALUES ('admin', crypt('admin', gen_salt('bf', 10)), true),
           ('pepe', crypt('pepe', gen_salt('bf', 10)), true),
           ('juan', crypt('juan', gen_salt('bf', 10)), false);

INSERT INTO categorias (categoria)
    VALUES ('Informatica'),
            ('Alimentación'),
            ('Ropa');