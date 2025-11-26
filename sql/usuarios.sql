-- Insert departamentos, provincias y distritos (si no están ya)
INSERT INTO ubdepartamento (departamento) VALUES ('Departamento1');
INSERT INTO ubprovincia (provincia, ubdepartamento) VALUES ('Provincia1', 1);
INSERT INTO ubdistrito (distrito, ubprovincia) VALUES ('Distrito1', 1);

-- Insert programas de estudio
INSERT INTO prog_estudios (nom_progest, perfilingre_progest, perfilegre_progest) VALUES
('ADMINSISPAGOS', 'Perfil ingreso ADMIN', 'Perfil egreso ADMIN'),
('BIENESTARSISPAGOS', 'Perfil ingreso BIENESTAR', 'Perfil egreso BIENESTAR'),
('DIRECIONSISPAGOS', 'Perfil ingreso DIRECION', 'Perfil egreso DIRECION');

-- Insert 3 empleados para cada programa
INSERT INTO empleado (prog_estudios, dni_emp, apnom_emp, sex_emp, cel_emp, ubigeodir_emp, ubigeonac_emp, dir_emp, mailp_emp, maili_emp, fecnac_emp, cargo_emp, cond_emp, id_progest, fecinc_emp, foto_emp, estado)
VALUES
(1, '12345678', 'Juan Perez', 'M', '987654321', '010101', '010101', 'Direccion 123', 'juan@mail.com', 'juan2@mail.com', '1980-01-01', 'A', 'B', 'ADM', '2020-01-01', 'foto.jpg', 1),
(2, '23456789', 'Maria Lopez', 'F', '987654320', '010102', '010102', 'Direccion 456', 'maria@mail.com', 'maria2@mail.com', '1985-02-02', 'B', 'A', 'BIE', '2021-02-01', 'foto2.jpg', 1),
(3, '34567890', 'Carlos Sanchez', 'M', '987654319', '010103', '010103', 'Direccion 789', 'carlos@mail.com', 'carlos2@mail.com', '1990-03-03', 'C', 'C', 'DIR', '2022-03-01', 'foto3.jpg', 1);

-- Insert un estudiante
INSERT INTO estudiante (ubdistrito, dni_est, ap_est, am_est, nom_est, sex_est, cel_est, ubigeodir_est, ubigeonac_est, dir_est, mailp_est, maili_est, fecnac_est, foto_est, estado)
VALUES (1, '87654321', 'Lopez', 'Gomez', 'Ana', 'F', '987654322', '010101', '010101', 'Direccion Estudiante', 'ana@mail.com', 'ana2@mail.com', '2000-05-15', 'foto_est.jpg', 1);

-- Insert usuarios para los 3 empleados y 1 estudiante
INSERT INTO usuarios (usuario, password, tipo, estuempleado, token) VALUES
('juanp', 'password1', 1, 1, NULL),
('marial', 'password2', 1, 2, NULL),
('carloss', 'password3', 1, 3, NULL),
('anae', 'password4', 2, 1, NULL);
