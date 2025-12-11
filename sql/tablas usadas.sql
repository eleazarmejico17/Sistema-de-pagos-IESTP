create database istc;
	use symetri;
	set sql_mode='';

	create table  ubdepartamento(
		id int primary key not null auto_increment,
		departamento varchar(250)
	);

	create table  ubprovincia(
		id int primary key not null auto_increment,
		provincia varchar(250),
		ubdepartamento int,
		foreign key (ubdepartamento) references ubdepartamento(id)
	);

	create table  ubdistrito(
		id int primary key not null auto_increment,
		distrito varchar(250),
		ubprovincia int,
		foreign key (ubprovincia) references ubprovincia(id)
	);

	create table  estudiante(
		id int primary key not null auto_increment,
		ubdistrito int,
		dni_est char(8),
		ap_est varchar(40),
		am_est varchar(40),
		nom_est varchar(40),
		sex_est char(1),
		cel_est  char(9),
		ubigeodir_est char(6),
		ubigeonac_est char(6),
		dir_est varchar(40),
		mailp_est varchar(40),
		maili_est varchar(40),
		fecnac_est date,
		foto_est varchar(40),
		estado int,
		foreign key (ubdistrito) references ubdistrito(id)
	);

	create table  prog_estudios(
		id int primary key not null auto_increment,
		nom_progest varchar(40),
		perfilingre_progest text,
		perfilegre_progest text
	);

	create table  empleado(
		id int primary key not null auto_increment,
		prog_estudios int,
		dni_emp char(8),
		apnom_emp varchar(60),
		sex_emp char(1),
		cel_emp char(9),
		ubigeodir_emp  char(6),
		ubigeonac_emp char(6),
		dir_emp varchar(40),
		mailp_emp varchar(40),
		maili_emp varchar(40) ,
		fecnac_emp date,
		cargo_emp char(1),
		cond_emp char(1),
		id_progest char(3),
		fecinc_emp date,
		foto_emp varchar(40),
		estado int,
		foreign key (prog_estudios) references prog_estudios(id)
	);

	create table  matricula(
		id int primary key not null auto_increment,
		estudiante int,
		prog_estudios int,
		id_matricula char(9),
		per_lectivo varchar(7),
		per_acad varchar(3),
		per_acad2 int(1),
		seccion char(1),
		turno char(1),
		fec_matricula date,
		cond_matricula char(1),
		est_matricula char(1),
		est_perlec char(1),
		obs_matricula varchar(50),
		foreign key (estudiante) references estudiante(id),
		foreign key (prog_estudios) references prog_estudios(id)
	);

	create table  usuarios(
		id int primary key not null auto_increment,
		usuario varchar(200),
		password text,
		tipo int, -- 1 ES EMPLEADO, 2 ES ESTUDIANTE, 3 ES EMPRESA  
		estuempleado int,
		token text
	);

	create table resoluciones(
		id int primary key not null auto_increment,
		numero_resolucion varchar(50) not null unique,
		titulo varchar(255) not null,
		texto_respaldo text,
		monto_descuento decimal(10,2),
		ruta_documento varchar(255),
		fecha_inicio date,
		fecha_fin date,
		creado_por int,
		creado_en datetime,
		foreign key (creado_por) references empleado(id)
	);

	create table solicitudes(
		id int primary key not null auto_increment,
		estudiante int not null,
		resoluciones int,
		tipo_solicitud varchar(100) not null,
		descripcion text,
		estado enum('pendiente','en_evaluacion','aprobado','rechazado') default 'pendiente',
		fecha_solicitud datetime,
		fecha_revision datetime,
		empleado int,
		observaciones text,
		foto text,
		foreign key (estudiante) references estudiante(id),
		foreign key (resoluciones) references resoluciones(id),
		foreign key (empleado) references empleado(id)
	);

create table historial_solicitudes(
	id int primary key not null auto_increment,
	solicitud_id int not null,
	estado varchar(50) not null,
	fecha datetime,
	empleado int,
	comentarios text,
	foreign key (solicitud_id) references solicitudes(id),
	foreign key (empleado) references empleado(id)
);

create table beneficiarios(
	id int primary key not null auto_increment,
	estudiante int not null,
	resoluciones int not null,
	porcentaje_descuento decimal(5,2) not null,
	fecha_inicio date,
	fecha_fin date,
	activo boolean default true,
	registrado_por int,
	registrado_en datetime,
	foreign key (estudiante) references estudiante(id),
	foreign key (resoluciones) references resoluciones(id),
	foreign key (registrado_por) references empleado(id)
);

create table historial_descuentos(
	id int primary key not null auto_increment,
	beneficiario_id int not null,
	monto_original decimal(10,2) not null,
	porcentaje_descuento decimal(5,2) not null,
	monto_descuento decimal(10,2) not null,
	monto_final decimal(10,2) not null,
	aplicado_por int,
	aplicado_en datetime,
	observaciones varchar(255),
	foreign key (beneficiario_id) references beneficiarios(id),
	foreign key (aplicado_por) references empleado(id)
);

create table tipo_pago(
	id int primary key not null auto_increment,
	nombre varchar(50) not null unique,
	descripcion varchar(150)
);

create table pagos(
	id int primary key not null auto_increment,
	estudiante int not null,
	solicitudes int,
	tipo_pago int not null,
	monto_original decimal(10,2) not null,
	monto_descuento decimal(10,2) default 0.00,
	monto_final decimal(10,2) not null,
	fecha_pago datetime,
	comprobante varchar(255),
	registrado_por int,
	registrado_en datetime,
	foreign key (estudiante) references estudiante(id),
	foreign key (solicitudes) references solicitudes(id),
	foreign key (tipo_pago) references tipo_pago(id),
	foreign key (registrado_por) references empleado(id)
);
-- ============================================================
-- UBICACIÓN
-- ============================================================

INSERT INTO ubdepartamento (departamento) VALUES
('Lima'),('Cusco'),('Arequipa'),('Piura'),('La Libertad'),
('Junín'),('Puno'),('Tacna'),('Moquegua'),('Lambayeque'),
('Ancash'),('Cajamarca'),('Ayacucho'),('Huancavelica'),('Huánuco'),
('Ica'),('Loreto'),('Madre de Dios'),('Pasco'),('Tumbes');

INSERT INTO ubprovincia (provincia, ubdepartamento) VALUES
('Lima',1),('Urubamba',2),('Arequipa',3),('Sullana',4),('Trujillo',5),
('Huancayo',6),('Juliaca',7),('Tacna',8),('Ilo',9),('Chiclayo',10),
('Huaraz',11),('Jaén',12),('Huamanga',13),('Huancavelica',14),('Tingo María',15),
('Ica',16),('Maynas',17),('Tambopata',18),('Oxapampa',19),('Tumbes',20);

INSERT INTO ubdistrito (distrito, ubprovincia) VALUES
('Miraflores',1),('Urubamba Centro',2),('Cercado Arequipa',3),('Bellavista',4),('Víctor Larco',5),
('El Tambo',6),('Santa María',7),('Ciudad Nueva',8),('Pampa Inalámbrica',9),('La Victoria',10),
('Independencia',11),('Pomahuaca',12),('Ayacucho Centro',13),('Ascensión',14),('Las Palmas',15),
('La Tinguiña',16),('Punchana',17),('Jorge Chávez',18),('Villa Rica',19),('Corrales',20);

-- ============================================================
-- PROGRAMAS DE ESTUDIOS
-- ============================================================

INSERT INTO prog_estudios (nom_progest, perfilingre_progest, perfilegre_progest) VALUES
('Diseño Web','Perfil ingreso','Perfil egreso'),
('Contabilidad','Perfil ingreso','Perfil egreso'),
('Administración','Perfil ingreso','Perfil egreso'),
('Enfermería','Perfil ingreso','Perfil egreso'),
('Mecánica','Perfil ingreso','Perfil egreso'),
('Electricidad','Perfil ingreso','Perfil egreso'),
('Marketing','Perfil ingreso','Perfil egreso'),
('Computación','Perfil ingreso','Perfil egreso'),
('Redes','Perfil ingreso','Perfil egreso'),
('Software','Perfil ingreso','Perfil egreso'),
('Gastronomía','Perfil ingreso','Perfil egreso'),
('Turismo','Perfil ingreso','Perfil egreso'),
('Minería','Perfil ingreso','Perfil egreso'),
('Forestal','Perfil ingreso','Perfil egreso'),
('Industrial','Perfil ingreso','Perfil egreso'),
('Logística','Perfil ingreso','Perfil egreso'),
('Seguridad','Perfil ingreso','Perfil egreso'),
('Diseño Gráfico','Perfil ingreso','Perfil egreso'),
('Topografía','Perfil ingreso','Perfil egreso'),
('Computación Empresarial','Perfil ingreso','Perfil egreso');

-- ============================================================
-- ESTUDIANTES
-- ============================================================

INSERT INTO estudiante (
    ubdistrito, dni_est, ap_est, am_est, nom_est, sex_est, cel_est,
    ubigeodir_est, ubigeonac_est, dir_est, mailp_est, maili_est,
    fecnac_est, foto_est, estado
) VALUES
(1,'12345601','Gomez','Ruiz','Carlos','M','900000001','150101','150101','Av Lima 111','est1@gmail.com','est1@istc.edu','2002-01-10','f1.jpg',1),
(2,'12345602','Perez','Luz','María','F','900000002','150102','150102','Av Cusco 222','est2@gmail.com','est2@istc.edu','2002-02-10','f2.jpg',1),
(3,'12345603','Sanchez','Rojas','Luis','M','900000003','150103','150103','Av Sol 333','est3@gmail.com','est3@istc.edu','2002-03-10','f3.jpg',1),
(4,'12345604','Huaman','Quispe','Ana','F','900000004','150104','150104','Av Grau 444','est4@gmail.com','est4@istc.edu','2002-04-10','f4.jpg',1),
(5,'12345605','Ramos','Mendoza','Andrea','F','900000005','150105','150105','Av Norte 555','est5@gmail.com','est5@istc.edu','2002-05-10','f5.jpg',1),
(6,'12345606','Torres','López','Julio','M','900000006','150106','150106','Av Centro 666','est6@gmail.com','est6@istc.edu','2002-06-10','f6.jpg',1),
(7,'12345607','Flores','Salas','Martin','M','900000007','150107','150107','Av Sur 777','est7@gmail.com','est7@istc.edu','2002-07-10','f7.jpg',1),
(8,'12345608','Diaz','Reyes','José','M','900000008','150108','150108','Av Paz 888','est8@gmail.com','est8@istc.edu','2002-08-10','f8.jpg',1),
(9,'12345609','Cruz','Silva','Lucía','F','900000009','150109','150109','Av Real 999','est9@gmail.com','est9@istc.edu','2002-09-10','f9.jpg',1),
(10,'12345610','Vargas','Acosta','Angela','F','900000010','150110','150110','Av Prado 100','est10@gmail.com','est10@istc.edu','2002-10-10','f10.jpg',1),
(11,'12345611','Ruiz','Molina','Pedro','M','900000011','150111','150111','Av Arica 101','est11@gmail.com','est11@istc.edu','2002-11-10','f11.jpg',1),
(12,'12345612','Mamani','Paco','Rosa','F','900000012','150112','150112','Av Lima 102','est12@gmail.com','est12@istc.edu','2002-12-10','f12.jpg',1),
(13,'12345613','Lazaro','Soto','Diego','M','900000013','150113','150113','Av Perú 103','est13@gmail.com','est13@istc.edu','2002-01-15','f13.jpg',1),
(14,'12345614','Acuña','Campos','Daniel','M','900000014','150114','150114','Av Zela 104','est14@gmail.com','est14@istc.edu','2002-02-15','f14.jpg',1),
(15,'12345615','Meza','Ayala','Sandra','F','900000015','150115','150115','Av Luna 105','est15@gmail.com','est15@istc.edu','2002-03-15','f15.jpg',1),
(16,'12345616','Valdez','Ramos','Lucero','F','900000016','150116','150116','Av Sur 106','est16@gmail.com','est16@istc.edu','2002-04-15','f16.jpg',1),
(17,'12345617','Mora','Vera','Cristian','M','900000017','150117','150117','Av Centro 107','est17@gmail.com','est17@istc.edu','2002-05-15','f17.jpg',1),
(18,'12345618','Soto','Quispe','Miguel','M','900000018','150118','150118','Av Sol 108','est18@gmail.com','est18@istc.edu','2002-06-15','f18.jpg',1),
(19,'12345619','Reyna','Nuñez','Carla','F','900000019','150119','150119','Av Norte 109','est19@gmail.com','est19@istc.edu','2002-07-15','f19.jpg',1),
(20,'12345620','Franco','Gomez','Paola','F','900000020','150120','150120','Av Lima 120','est20@gmail.com','est20@istc.edu','2002-08-15','f20.jpg',1);

-- ============================================================
-- EMPLEADOS (20)
-- ============================================================

INSERT INTO empleado (
    prog_estudios, dni_emp, apnom_emp, sex_emp, cel_emp,
    ubigeodir_emp, ubigeonac_emp, dir_emp, mailp_emp, maili_emp,
    fecnac_emp, cargo_emp, cond_emp, id_progest, fecinc_emp,
    foto_emp, estado
) VALUES
(1,'44556601','Perez López Juan','M','910000001','150101','150101','Av A 1','emp1@gmail.com','emp1@istc.edu','1985-01-01','A','1','001','2020-01-01','e1.jpg',1),
(2,'44556602','Sosa Silva Maria','F','910000002','150102','150102','Av A 2','emp2@gmail.com','emp2@istc.edu','1985-02-01','A','1','002','2020-02-01','e2.jpg',1),
(3,'44556603','Torres Paredes Luis','M','910000003','150103','150103','Av A 3','emp3@gmail.com','emp3@istc.edu','1985-03-01','A','1','003','2020-03-01','e3.jpg',1),
(4,'44556604','Ramos Guzman Ana','F','910000004','150104','150104','Av A 4','emp4@gmail.com','emp4@istc.edu','1985-04-01','A','1','004','2020-04-01','e4.jpg',1),
(5,'44556605','Cruz Lopez Mateo','M','910000005','150105','150105','Av A 5','emp5@gmail.com','emp5@istc.edu','1985-05-01','A','1','005','2020-05-01','e5.jpg',1),
(6,'44556606','Flores Vega Lucia','F','910000006','150106','150106','Av A 6','emp6@gmail.com','emp6@istc.edu','1985-06-01','A','1','006','2020-06-01','e6.jpg',1),
(7,'44556607','Soto Rojas Pedro','M','910000007','150107','150107','Av A 7','emp7@gmail.com','emp7@istc.edu','1985-07-01','A','1','007','2020-07-01','e7.jpg',1),
(8,'44556608','Cáceres Poma Rosa','F','910000008','150108','150108','Av A 8','emp8@gmail.com','emp8@istc.edu','1985-08-01','A','1','008','2020-08-01','e8.jpg',1),
(9,'44556609','Nuñez Alvarez Jorge','M','910000009','150109','150109','Av A 9','emp9@gmail.com','emp9@istc.edu','1985-09-01','A','1','009','2020-09-01','e9.jpg',1),
(10,'44556610','Vega Cano Karla','F','910000010','150110','150110','Av A 10','emp10@gmail.com','emp10@istc.edu','1985-10-01','A','1','010','2020-10-01','e10.jpg',1),
(11,'44556611','Palacios Fajardo Luis','M','910000011','150111','150111','Av A 11','emp11@gmail.com','emp11@istc.edu','1985-11-01','A','1','011','2020-11-01','e11.jpg',1),
(12,'44556612','Tello Zuniga Maria','F','910000012','150112','150112','Av A 12','emp12@gmail.com','emp12@istc.edu','1985-12-01','A','1','012','2020-12-01','e12.jpg',1),
(13,'44556613','Zevallos Jara Juan','M','910000013','150113','150113','Av A 13','emp13@gmail.com','emp13@istc.edu','1986-01-01','A','1','013','2021-01-01','e13.jpg',1),
(14,'44556614','Marquez Soto Laura','F','910000014','150114','150114','Av A 14','emp14@gmail.com','emp14@istc.edu','1986-02-01','A','1','014','2021-02-01','e14.jpg',1),
(15,'44556615','Matos Reyes Miguel','M','910000015','150115','150115','Av A 15','emp15@gmail.com','emp15@istc.edu','1986-03-01','A','1','015','2021-03-01','e15.jpg',1),
(16,'44556616','Quiroz Castro Milagros','F','910000016','150116','150116','Av A 16','emp16@gmail.com','emp16@istc.edu','1986-04-01','A','1','016','2021-04-01','e16.jpg',1),
(17,'44556617','Arias Torres Bryan','M','910000017','150117','150117','Av A 17','emp17@gmail.com','emp17@istc.edu','1986-05-01','A','1','017','2021-05-01','e17.jpg',1),
(18,'44556618','Campos Silva Diana','F','910000018','150118','150118','Av A 18','emp18@gmail.com','emp18@istc.edu','1986-06-01','A','1','018','2021-06-01','e18.jpg',1),
(19,'44556619','Cortez Peña Cesar','M','910000019','150119','150119','Av A 19','emp19@gmail.com','emp19@istc.edu','1986-07-01','A','1','019','2021-07-01','e19.jpg',1),
(20,'44556620','Garcia Barrios Paola','F','910000020','150120','150120','Av A 20','emp20@gmail.com','emp20@istc.edu','1986-08-01','A','1','020','2021-08-01','e20.jpg',1);

-- ============================================================
-- USUARIOS (20)
-- ============================================================

INSERT INTO usuarios (usuario, password, tipo, estuempleado, token) VALUES
('usuario1','123456',1,1,NULL),
('usuario2','123456',1,2,NULL),
('usuario3','123456',1,3,NULL),
('usuario4','123456',1,4,NULL),
('usuario5','123456',1,5,NULL),
('usuario6','123456',1,6,NULL),
('usuario7','123456',1,7,NULL),
('usuario8','123456',1,8,NULL),
('usuario9','123456',1,9,NULL),
('usuario10','123456',1,10,NULL),
('usuario11','123456',2,1,NULL),
('usuario12','123456',2,2,NULL),
('usuario13','123456',2,3,NULL),
('usuario14','123456',2,4,NULL),
('usuario15','123456',2,5,NULL),
('usuario16','123456',2,6,NULL),
('usuario17','123456',2,7,NULL),
('usuario18','123456',2,8,NULL),
('usuario19','123456',2,9,NULL),
('usuario20','123456',2,10,NULL);

-- ============================================================
-- RESOLUCIONES (20)
-- ============================================================

INSERT INTO resoluciones (
    numero_resolucion, titulo, texto_respaldo,
    monto_descuento, ruta_documento, fecha_inicio,
    fecha_fin, creado_por, creado_en
) VALUES
('RES-001','Descuento Hermano','Texto...',50,'res1.pdf','2025-01-01','2025-12-31',1,NOW()),
('RES-002','Excelencia Académica','Texto...',40,'res2.pdf','2025-01-01','2025-12-31',2,NOW()),
('RES-003','Deporte','Texto...',30,'res3.pdf','2025-01-01','2025-12-31',3,NOW()),
('RES-004','Arte','Texto...',25,'res4.pdf','2025-01-01','2025-12-31',4,NOW()),
('RES-005','Cultura','Texto...',20,'res5.pdf','2025-01-01','2025-12-31',5,NOW()),
('RES-006','Apoyo Social','Texto...',35,'res6.pdf','2025-01-01','2025-12-31',6,NOW()),
('RES-007','Provincia','Texto...',15,'res7.pdf','2025-01-01','2025-12-31',7,NOW()),
('RES-008','Investigación','Texto...',30,'res8.pdf','2025-01-01','2025-12-31',8,NOW()),
('RES-009','Voluntariado','Texto...',10,'res9.pdf','2025-01-01','2025-12-31',9,NOW()),
('RES-010','Reconocimiento','Texto...',5,'res10.pdf','2025-01-01','2025-12-31',10,NOW()),
('RES-011','Beca Completa','Texto...',100,'res11.pdf','2025-01-01','2025-12-31',11,NOW()),
('RES-012','Media Beca','Texto...',50,'res12.pdf','2025-01-01','2025-12-31',12,NOW()),
('RES-013','Descuento Especial','Texto...',18,'res13.pdf','2025-01-01','2025-12-31',13,NOW()),
('RES-014','Descuento ITS','Texto...',12,'res14.pdf','2025-01-01','2025-12-31',14,NOW()),
('RES-015','Descuento Temporada','Texto...',27,'res15.pdf','2025-01-01','2025-12-31',15,NOW()),
('RES-016','Promo 1','Texto...',22,'res16.pdf','2025-01-01','2025-12-31',16,NOW()),
('RES-017','Promo 2','Texto...',17,'res17.pdf','2025-01-01','2025-12-31',17,NOW()),
('RES-018','Promo 3','Texto...',42,'res18.pdf','2025-01-01','2025-12-31',18,NOW()),
('RES-019','Promo 4','Texto...',31,'res19.pdf','2025-01-01','2025-12-31',19,NOW()),
('RES-020','Promo 5','Texto...',29,'res20.pdf','2025-01-01','2025-12-31',20,NOW());

-- ============================================================
-- SOLICITUDES (20)
-- ============================================================

INSERT INTO solicitudes (
    estudiante, resoluciones, tipo_solicitud,
    descripcion, estado, fecha_solicitud,
    fecha_revision, empleado, observaciones, foto
) VALUES
(1,1,'Descuento','Solicitud 1','pendiente',NOW(),NULL,1,NULL,NULL),
(2,2,'Descuento','Solicitud 2','pendiente',NOW(),NULL,2,NULL,NULL),
(3,3,'Descuento','Solicitud 3','pendiente',NOW(),NULL,3,NULL,NULL),
(4,4,'Descuento','Solicitud 4','pendiente',NOW(),NULL,4,NULL,NULL),
(5,5,'Descuento','Solicitud 5','pendiente',NOW(),NULL,5,NULL,NULL),
(6,6,'Descuento','Solicitud 6','pendiente',NOW(),NULL,6,NULL,NULL),
(7,7,'Descuento','Solicitud 7','pendiente',NOW(),NULL,7,NULL,NULL),
(8,8,'Descuento','Solicitud 8','pendiente',NOW(),NULL,8,NULL,NULL),
(9,9,'Descuento','Solicitud 9','pendiente',NOW(),NULL,9,NULL,NULL),
(10,10,'Descuento','Solicitud 10','pendiente',NOW(),NULL,10,NULL,NULL),
(11,11,'Descuento','Solicitud 11','pendiente',NOW(),NULL,11,NULL,NULL),
(12,12,'Descuento','Solicitud 12','pendiente',NOW(),NULL,12,NULL,NULL),
(13,13,'Descuento','Solicitud 13','pendiente',NOW(),NULL,13,NULL,NULL),
(14,14,'Descuento','Solicitud 14','pendiente',NOW(),NULL,14,NULL,NULL),
(15,15,'Descuento','Solicitud 15','pendiente',NOW(),NULL,15,NULL,NULL),
(16,16,'Descuento','Solicitud 16','pendiente',NOW(),NULL,16,NULL,NULL),
(17,17,'Descuento','Solicitud 17','pendiente',NOW(),NULL,17,NULL,NULL),
(18,18,'Descuento','Solicitud 18','pendiente',NOW(),NULL,18,NULL,NULL),
(19,19,'Descuento','Solicitud 19','pendiente',NOW(),NULL,19,NULL,NULL),
(20,20,'Descuento','Solicitud 20','pendiente',NOW(),NULL,20,NULL,NULL);

-- ============================================================
-- HISTORIAL SOLICITUDES (20)
-- ============================================================

INSERT INTO historial_solicitudes (solicitud_id, estado, fecha, empleado, comentarios) VALUES
(1,'pendiente',NOW(),1,'Registrado'),
(2,'pendiente',NOW(),2,'Registrado'),
(3,'pendiente',NOW(),3,'Registrado'),
(4,'pendiente',NOW(),4,'Registrado'),
(5,'pendiente',NOW(),5,'Registrado'),
(6,'pendiente',NOW(),6,'Registrado'),
(7,'pendiente',NOW(),7,'Registrado'),
(8,'pendiente',NOW(),8,'Registrado'),
(9,'pendiente',NOW(),9,'Registrado'),
(10,'pendiente',NOW(),10,'Registrado'),
(11,'pendiente',NOW(),11,'Registrado'),
(12,'pendiente',NOW(),12,'Registrado'),
(13,'pendiente',NOW(),13,'Registrado'),
(14,'pendiente',NOW(),14,'Registrado'),
(15,'pendiente',NOW(),15,'Registrado'),
(16,'pendiente',NOW(),16,'Registrado'),
(17,'pendiente',NOW(),17,'Registrado'),
(18,'pendiente',NOW(),18,'Registrado'),
(19,'pendiente',NOW(),19,'Registrado'),
(20,'pendiente',NOW(),20,'Registrado');

-- ============================================================
-- BENEFICIARIOS (20)
-- ============================================================

INSERT INTO beneficiarios (
    estudiante, resoluciones, porcentaje_descuento,
    fecha_inicio, fecha_fin, activo, registrado_por, registrado_en
) VALUES
(1,1,50,'2025-01-01','2025-12-31',1,1,NOW()),
(2,2,40,'2025-01-01','2025-12-31',1,2,NOW()),
(3,3,30,'2025-01-01','2025-12-31',1,3,NOW()),
(4,4,25,'2025-01-01','2025-12-31',1,4,NOW()),
(5,5,20,'2025-01-01','2025-12-31',1,5,NOW()),
(6,6,35,'2025-01-01','2025-12-31',1,6,NOW()),
(7,7,15,'2025-01-01','2025-12-31',1,7,NOW()),
(8,8,30,'2025-01-01','2025-12-31',1,8,NOW()),
(9,9,10,'2025-01-01','2025-12-31',1,9,NOW()),
(10,10,5,'2025-01-01','2025-12-31',1,10,NOW()),
(11,11,100,'2025-01-01','2025-12-31',1,11,NOW()),
(12,12,50,'2025-01-01','2025-12-31',1,12,NOW()),
(13,13,18,'2025-01-01','2025-12-31',1,13,NOW()),
(14,14,12,'2025-01-01','2025-12-31',1,14,NOW()),
(15,15,27,'2025-01-01','2025-12-31',1,15,NOW()),
(16,16,22,'2025-01-01','2025-12-31',1,16,NOW()),
(17,17,17,'2025-01-01','2025-12-31',1,17,NOW()),
(18,18,42,'2025-01-01','2025-12-31',1,18,NOW()),
(19,19,31,'2025-01-01','2025-12-31',1,19,NOW()),
(20,20,29,'2025-01-01','2025-12-31',1,20,NOW());

-- ============================================================
-- HISTORIAL DESCUENTOS (20)
-- ============================================================

INSERT INTO historial_descuentos (
    beneficiario_id, monto_original, porcentaje_descuento,
    monto_descuento, monto_final, aplicado_por, aplicado_en, observaciones
) VALUES
(1,300,50,150,150,1,NOW(),'OK'),
(2,400,40,160,240,2,NOW(),'OK'),
(3,350,30,105,245,3,NOW(),'OK'),
(4,280,25,70,210,4,NOW(),'OK'),
(5,200,20,40,160,5,NOW(),'OK'),
(6,500,35,175,325,6,NOW(),'OK'),
(7,250,15,37.50,212.50,7,NOW(),'OK'),
(8,300,30,90,210,8,NOW(),'OK'),
(9,350,10,35,315,9,NOW(),'OK'),
(10,280,5,14,266,10,NOW(),'OK'),
(11,300,100,300,0,11,NOW(),'OK'),
(12,400,50,200,200,12,NOW(),'OK'),
(13,450,18,81,369,13,NOW(),'OK'),
(14,220,12,26.4,193.6,14,NOW(),'OK'),
(15,500,27,135,365,15,NOW(),'OK'),
(16,450,22,99,351,16,NOW(),'OK'),
(17,300,17,51,249,17,NOW(),'OK'),
(18,600,42,252,348,18,NOW(),'OK'),
(19,320,31,99.2,220.8,19,NOW(),'OK'),
(20,410,29,118.9,291.1,20,NOW(),'OK');

-- ============================================================
-- TIPO DE PAGO (20)
-- ============================================================

INSERT INTO tipo_pago (nombre, descripcion) VALUES
('Matrícula','Pago de matrícula'),
('Pensión','Pago mensual'),
('Certificado','Certificados académicos'),
('Constancia','Constancias varias'),
('Duplicado','Duplicados oficiales'),
('Carnet','Carnet institucional'),
('Biblioteca','Multas o pagos biblioteca'),
('Taller','Talleres'),
('Laboratorio','Uso de laboratorio'),
('Especial','Pago especial'),
('Trámite 1','Trámite extra'),
('Trámite 2','Trámite extra'),
('Trámite 3','Trámite extra'),
('Trámite 4','Trámite extra'),
('Trámite 5','Trámite extra'),
('Trámite 6','Trámite extra'),
('Trámite 7','Trámite extra'),
('Trámite 8','Trámite extra'),
('Trámite 9','Trámite extra'),
('Trámite 10','Trámite extra');

-- ============================================================
-- PAGOS (20)
-- ============================================================

INSERT INTO pagos (
    estudiante, solicitudes, tipo_pago,
    monto_original, monto_descuento, monto_final,
    fecha_pago, comprobante, registrado_por, registrado_en
) VALUES
(1,1,1,300,150,150,NOW(),'comp1.jpg',1,NOW()),
(2,2,2,400,160,240,NOW(),'comp2.jpg',2,NOW()),
(3,3,3,350,105,245,NOW(),'comp3.jpg',3,NOW()),
(4,4,4,280,70,210,NOW(),'comp4.jpg',4,NOW()),
(5,5,5,200,40,160,NOW(),'comp5.jpg',5,NOW()),
(6,6,6,500,175,325,NOW(),'comp6.jpg',6,NOW()),
(7,7,7,250,37.5,212.5,NOW(),'comp7.jpg',7,NOW()),
(8,8,8,300,90,210,NOW(),'comp8.jpg',8,NOW()),
(9,9,9,350,35,315,NOW(),'comp9.jpg',9,NOW()),
(10,10,10,280,14,266,NOW(),'comp10.jpg',10,NOW()),
(11,11,11,300,300,0,NOW(),'comp11.jpg',11,NOW()),
(12,12,12,400,200,200,NOW(),'comp12.jpg',12,NOW()),
(13,13,13,450,81,369,NOW(),'comp13.jpg',13,NOW()),
(14,14,14,220,26.4,193.6,NOW(),'comp14.jpg',14,NOW()),
(15,15,15,500,135,365,NOW(),'comp15.jpg',15,NOW()),
(16,16,16,450,99,351,NOW(),'comp16.jpg',16,NOW()),
(17,17,17,300,51,249,NOW(),'comp17.jpg',17,NOW()),
(18,18,18,600,252,348,NOW(),'comp18.jpg',18,NOW()),
(19,19,19,320,99.2,220.8,NOW(),'comp19.jpg',19,NOW()),
(20,20,20,410,118.9,291.1,NOW(),'comp20.jpg',20,NOW());
