<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte SST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f4f9; font-family: sans-serif; }
        .top-header { background: #0f172a; color: white; padding: 20px; text-align: center; border-bottom: 5px solid #2563eb; }
        .container { max-width: 800px; margin-top: 30px; padding-bottom: 50px; }
        .card { border: none; shadow: 0 4px 10px rgba(0,0,0,0.1); padding: 30px; background: white; border-radius: 10px; }
        .section-title { color: #2563eb; font-weight: bold; border-bottom: 1px solid #ddd; margin-top: 20px; margin-bottom: 15px; padding-bottom: 5px; }
        label { font-weight: 600; font-size: 0.9rem; color: #444; margin-top: 10px; }
        .btn-enviar { background: #0f172a; color: white; padding: 15px; width: 100%; border: none; font-weight: bold; border-radius: 5px; margin-top: 20px; }
        .btn-enviar:hover { background: #2563eb; }
        .alert-action { background: #e0f2fe; border-left: 5px solid #2563eb; padding: 15px; margin: 15px 0; }
    </style>
</head>
<body>

    <div class="top-header">
        <h2 class="m-0 fw-bold">REPORTE DE SEGURIDAD</h2>
        <small>SISTEMA DE GESTIÓN SST - VITAPRO</small>
    </div>

    <div class="container">
        <form action="api/guardar.php" method="POST" enctype="multipart/form-data" class="card">
            
            <div class="section-title">1. DATOS GENERALES</div>
            <div class="row">
                <div class="col-md-6">
                    <label>Reportado Por *</label>
                    <input type="text" name="nombre" class="form-control" required placeholder="Nombre Completo">
                </div>
                <div class="col-md-6">
                    <label>Área *</label>
                    <select name="area" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <option value="Producción">Producción</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                        <option value="Logística">Logística</option>
                        <option value="Calidad">Calidad</option>
                        <option value="Administración">Administración</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Tipo Usuario</label>
                    <select name="tipo_usuario" class="form-select">
                        <option value="Interno">Interno</option>
                        <option value="Contratista">Contratista</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Empresa (Solo contratistas)</label>
                    <input type="text" name="empresa" class="form-control">
                </div>
            </div>

            <div class="section-title">2. EL HALLAZGO</div>
            <div class="row">
                <div class="col-md-6">
                    <label>Tipo *</label>
                    <select name="hallazgo" class="form-select" required>
                        <option value="Acto Subestándar">Acto Subestándar</option>
                        <option value="Condición Subestándar">Condición Subestándar</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Clasificación (Causa)</label>
                    <select name="causa_especifica" class="form-select">
                        <option value="General">General</option>
                        <option value="EPP">EPP</option>
                        <option value="Herramientas">Herramientas</option>
                        <option value="Orden y Limpieza">Orden y Limpieza</option>
                        <option value="Procedimiento">Procedimiento</option>
                    </select>
                </div>
                <div class="col-12">
                    <label>Descripción del Problema *</label>
                    <textarea name="descripcion" class="form-control" rows="3" required></textarea>
                </div>
            </div>

            <div class="section-title">3. ACCIÓN Y CIERRE</div>
            
            <div class="alert-action">
                <label style="margin-top:0; color:#0369a1;">✅ Acción Correctiva Inmediata *</label>
                <textarea name="accion_inmediata" class="form-control" rows="2" required placeholder="¿Qué hizo usted para controlar el riesgo en el momento?"></textarea>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <label>Nivel Riesgo</label>
                    <select name="riesgo" class="form-select">
                        <option value="Bajo">Bajo</option>
                        <option value="Medio">Medio</option>
                        <option value="Alto">Alto</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>¿Detuvo Actividad?</label>
                    <select name="detuvo" class="form-select">
                        <option value="NO">NO</option>
                        <option value="SI">SI</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Aviso SAP</label>
                    <input type="text" name="sap" class="form-control">
                </div>
            </div>

            <div class="section-title">4. EVIDENCIA</div>
            <input type="file" name="foto" class="form-control" accept="image/*">

            <button type="submit" class="btn-enviar">ENVIAR REPORTE</button>
        </form>
    </div>
</body>
</html>
