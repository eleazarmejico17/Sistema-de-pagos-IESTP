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
