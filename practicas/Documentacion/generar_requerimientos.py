# -*- coding: utf-8 -*-
"""
=====================================================================
 GENERADOR DEL DOCUMENTO DE REQUERIMIENTOS
 Sistema Privado de Gestion y Consulta Documental - Version 2.0
 Sin componentes de inteligencia artificial
=====================================================================

Genera DOS archivos en la carpeta actual:
   - Requerimientos_v2_SinIA.docx   (Word, editable)
   - Requerimientos_v2_SinIA.pdf    (PDF, para entregar)

COMO USARLO
-----------
1. Abra una terminal en esta carpeta.
2. Ejecute estos dos comandos:

       python -m pip install python-docx reportlab
       python generar_requerimientos.py

3. Los dos archivos aparecen en esta misma carpeta.

NOTA: si `python` no funciona, pruebe con `py` en Windows.
=====================================================================
"""

import os
import sys
import importlib.util

try:
    sys.stdout.reconfigure(encoding='utf-8')
except Exception:
    pass

CARPETA = os.path.dirname(os.path.abspath(__file__))
ARCHIVO_DOCX = os.path.join(CARPETA, 'Requerimientos_v2_SinIA.docx')
ARCHIVO_PDF = os.path.join(CARPETA, 'Requerimientos_v2_SinIA.pdf')


def cargar_modulo(nombre_archivo):
    """Carga un modulo .py ubicado en esta misma carpeta."""
    ruta = os.path.join(CARPETA, nombre_archivo)
    if not os.path.exists(ruta):
        raise FileNotFoundError('No se encontro el archivo %s' % nombre_archivo)

    spec = importlib.util.spec_from_file_location(
        nombre_archivo.replace('.py', ''), ruta)
    modulo = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(modulo)
    return modulo


def main():
    print('=' * 68)
    print(' GENERADOR DE REQUERIMIENTOS')
    print(' Sistema Privado de Gestion y Consulta Documental - v2.0')
    print('=' * 68)
    print('')

    # -----------------------------------------------------------------
    # 1. Verificar dependencias
    # -----------------------------------------------------------------
    faltantes = []
    try:
        import docx  # noqa: F401
    except ImportError:
        faltantes.append('python-docx')
    try:
        import reportlab  # noqa: F401
    except ImportError:
        faltantes.append('reportlab')

    if faltantes:
        print('ERROR: faltan las librerias: %s' % ', '.join(faltantes))
        print('')
        print('Ejecute este comando y vuelva a intentarlo:')
        print('    python -m pip install python-docx reportlab')
        return 1

    # -----------------------------------------------------------------
    # 2. Construir el contenido
    # -----------------------------------------------------------------
    print('[1/3] Construyendo el contenido del documento...')

    contenido = [('portada',)]

    for archivo in ('_contenido1.py', '_contenido2.py', '_contenido3.py'):
        modulo = cargar_modulo(archivo)
        modulo.agregar(contenido.append)
        print('      - %s cargado' % archivo)

    print('      %d elementos de contenido generados.' % len(contenido))
    print('')

    render = cargar_modulo('_render.py')

    # -----------------------------------------------------------------
    # 3. Generar el DOCX
    # -----------------------------------------------------------------
    print('[2/3] Generando el documento Word (.docx)...')
    try:
        render.renderizar_docx(contenido, ARCHIVO_DOCX)
        peso = os.path.getsize(ARCHIVO_DOCX) / 1024
        print('      OK: %s (%.1f KB)' % (os.path.basename(ARCHIVO_DOCX), peso))
    except Exception as error:
        print('      ERROR al generar el DOCX: %s' % error)
        return 1

    print('')

    # -----------------------------------------------------------------
    # 4. Generar el PDF
    # -----------------------------------------------------------------
    print('[3/3] Generando el documento PDF...')
    try:
        render.renderizar_pdf(contenido, ARCHIVO_PDF)
        peso = os.path.getsize(ARCHIVO_PDF) / 1024
        print('      OK: %s (%.1f KB)' % (os.path.basename(ARCHIVO_PDF), peso))
    except Exception as error:
        print('      ERROR al generar el PDF: %s' % error)
        print('      El archivo .docx si se genero correctamente.')
        return 1

    # -----------------------------------------------------------------
    # Resumen
    # -----------------------------------------------------------------
    print('')
    print('=' * 68)
    print(' PROCESO COMPLETADO')
    print('=' * 68)
    print('')
    print(' Archivos generados en:')
    print('   %s' % CARPETA)
    print('')
    print('   1. %s' % os.path.basename(ARCHIVO_DOCX))
    print('   2. %s' % os.path.basename(ARCHIVO_PDF))
    print('')
    print(' CAMBIOS APLICADOS RESPECTO AL DOCUMENTO ORIGINAL:')
    print('   - Se elimino todo el contenido de inteligencia artificial.')
    print('   - Se retiraron RAG, embeddings, LLM, OCR y base vectorial.')
    print('   - La tecnologia se limito a HTML, CSS, PHP y MySQL Workbench.')
    print('   - El chat se reemplazo por busqueda con palabras clave y filtros.')
    print('')
    return 0


if __name__ == '__main__':
    sys.exit(main())


# ---------------------------------------------------------------------
#  CONTENIDO ANTIGUO (se conserva solo como referencia de formato)
#  El contenido real se encuentra en _contenido1.py, _contenido2.py
#  y _contenido3.py, que se cargan desde main().
# ---------------------------------------------------------------------
if False:
    CONTENIDO = []
    C = CONTENIDO.append


# ---------------------------------------------------------------------
# PORTADA
# ---------------------------------------------------------------------
C(('portada',))


# ---------------------------------------------------------------------
# CONTROL DE VERSIONES
# ---------------------------------------------------------------------
C(('titulo', 1, 'Control de versiones del documento'))
C(('tabla',
   ['Version', 'Fecha', 'Descripcion del cambio'],
   [['1.0', '23/09/2026',
     'Version inicial: sistema de consulta documental con inteligencia '
     'artificial (RAG, embeddings, chat en lenguaje natural).'],
    ['2.0', '30/09/2026',
     'Eliminacion total de los componentes de inteligencia artificial. '
     'Tecnologias reducidas a HTML, CSS, PHP y MySQL (Workbench). La '
     'consulta documental pasa a resolverse mediante busqueda por palabras '
     'clave y filtros avanzados.']],
   [2.0, 2.8, 11.4]))
C(('aviso', 'Alcance del cambio de la version 2.0',
   'Se retiraron por completo el motor RAG, los embeddings, la base vectorial, '
   'el modelo de lenguaje, el chat conversacional y el reconocimiento optico de '
   'caracteres (OCR). El sistema conserva la gestion documental, el control de '
   'acceso por roles, la trazabilidad y las copias de seguridad, pero la '
   'recuperacion de informacion se realiza con mecanismos deterministas de '
   'busqueda sobre texto, sin ninguna inferencia automatica.',
   'DBEAF7'))

# ---------------------------------------------------------------------
# CONTENIDO
# ---------------------------------------------------------------------
C(('titulo', 1, 'Contenido'))
for linea in [
    '1. Introduccion', '2. Descripcion de la necesidad',
    '3. Planteamiento del problema', '4. Objetivos', '5. Alcance',
    '6. Actores del sistema', '7. Requerimientos funcionales',
    '8. Requerimientos no funcionales', '9. Reglas de negocio',
    '10. Casos de uso principales', '11. Arquitectura conceptual',
    '12. Seguridad', '13. Flujo de la consulta documental',
    '14. Infraestructura y tecnologia', '15. Modelo de datos',
    '16. Criterios de aceptacion', '17. Riesgos', '18. Fases del proyecto',
    '19. Informacion pendiente de validar',
]:
    C(('contenido', linea))
C(('salto',))

# ---------------------------------------------------------------------
# 1. INTRODUCCION
# ---------------------------------------------------------------------
C(('titulo', 1, '1. Introduccion'))
C(('parrafo',
   'El presente documento define los requerimientos para un sistema privado de '
   'gestion y consulta documental. La solucion permite a una organizacion '
   'centralizar sus documentos institucionales, controlar el acceso por roles y '
   'areas, y localizar informacion mediante un buscador por palabras clave con '
   'filtros avanzados.'))
C(('parrafo',
   'La aplicacion esta construida exclusivamente con tecnologias web estandar: '
   'HTML para la estructura, CSS para la presentacion, PHP para la logica de '
   'servidor y MySQL como motor de base de datos, administrado mediante MySQL '
   'Workbench. No incorpora componentes de inteligencia artificial.'))
C(('aviso', 'Nota sobre el alcance tecnologico',
   'Este documento reemplaza la version 1.0, que contemplaba un sistema de '
   'consulta basado en inteligencia artificial. En la version actual no existe '
   'chat conversacional, ni generacion automatica de respuestas, ni modelos de '
   'lenguaje. Toda la informacion que el usuario recibe proviene directamente '
   'de los documentos registrados por la organizacion.'))

# ---------------------------------------------------------------------
# 2. DESCRIPCION DE LA NECESIDAD
# ---------------------------------------------------------------------
C(('titulo', 1, '2. Descripcion de la necesidad'))
C(('parrafo',
   'La organizacion administra un volumen elevado de documentos que dificulta '
   'la consulta manual, el acceso oportuno a informacion especifica y la '
   'trazabilidad de las operaciones. Los documentos se encuentran dispersos '
   'entre carpetas compartidas, correos electronicos y archivos locales, sin un '
   'control uniforme de acceso ni un registro de quien consulto que.'))
C(('parrafo', 'Se requiere un repositorio documental centralizado que permita:'))
for texto in [
    'Registrar cada documento con metadatos completos y verificables.',
    'Clasificar los documentos por categoria, caso, area y etiquetas.',
    'Restringir el acceso segun el rol y el area del usuario.',
    'Localizar documentos rapidamente mediante un buscador por palabras clave.',
    'Conservar el historial de versiones de cada documento.',
    'Registrar en una bitacora todas las operaciones criticas.',
    'Generar copias de seguridad recuperables.',
]:
    C(('vineta', texto))

# ---------------------------------------------------------------------
# 3. PLANTEAMIENTO DEL PROBLEMA
# ---------------------------------------------------------------------
C(('titulo', 1, '3. Planteamiento del problema'))
C(('parrafo',
   'El alto volumen documental y la ausencia de un repositorio controlado '
   'generan las siguientes consecuencias:'))
C(('tabla', ['Situacion actual', 'Consecuencia'],
   [['Documentos dispersos en multiples ubicaciones',
     'Perdida de tiempo en la busqueda manual y dificultad para localizar '
     'antecedentes.'],
    ['Ausencia de control de acceso',
     'Documentos reservados al alcance de personal no autorizado, con riesgo '
     'de incumplimiento normativo.'],
    ['Sin registro de operaciones',
     'Imposibilidad de auditar quien consulto, modifico o elimino un documento '
     'y en que momento.'],
    ['Multiples versiones sin control',
     'Uso de versiones desactualizadas y riesgo de omitir informacion '
     'relevante.'],
    ['Sin respaldos sistematicos',
     'Riesgo de perdida definitiva de informacion institucional.']],
   [6.0, 10.2]))

# ---------------------------------------------------------------------
# 4. OBJETIVOS
# ---------------------------------------------------------------------
C(('titulo', 1, '4. Objetivos'))
C(('titulo', 2, '4.1. Objetivo general'))
C(('parrafo',
   'Disenar y desarrollar un sistema privado de gestion y consulta documental '
   'que permita a los usuarios autorizados centralizar, clasificar, localizar y '
   'consultar la informacion institucional, con control de acceso por roles, '
   'trazabilidad completa de las operaciones y mecanismos de respaldo, '
   'utilizando unicamente HTML, CSS, PHP y MySQL.'))
C(('titulo', 2, '4.2. Objetivos especificos'))
for texto in [
    'Centralizar los documentos institucionales en un repositorio unico y controlado.',
    'Permitir la carga, clasificacion, actualizacion, versionado y retiro de documentos.',
    'Implementar autenticacion y control de acceso basado en roles y areas.',
    'Desarrollar un buscador por palabras clave con filtros avanzados.',
    'Extraer el texto de los archivos cargados para ampliar la busqueda al contenido.',
    'Registrar el historial de versiones de cada documento.',
    'Mantener una bitacora de auditoria de todas las acciones criticas.',
    'Generar y restaurar copias de seguridad de la base de datos.',
    'Proveer indicadores y reportes exportables sobre el inventario documental.',
    'Garantizar la integridad de los archivos mediante verificacion de hash.',
]:
    C(('numerada', texto))
C(('aviso', 'Objetivos retirados de la version anterior',
   'Se elimina el objetivo de "implementar recuperacion semantica de fragmentos" '
   'y el de "generar respuestas basadas en contenido recuperado". La busqueda '
   'actual es lexica y determinista: localiza coincidencias exactas de terminos '
   'en los metadatos y en el texto de los documentos.', 'FBE0E0'))
C(('salto',))

# ---------------------------------------------------------------------
# 5. ALCANCE
# ---------------------------------------------------------------------
C(('titulo', 1, '5. Alcance'))
C(('titulo', 2, '5.1. Incluye'))
C(('tabla', ['Modulo', 'Descripcion'],
   [['Autenticacion',
     'Inicio y cierre de sesion con credenciales validadas, control de intentos '
     'fallidos y cierre automatico por inactividad.'],
    ['Gestion de usuarios',
     'Creacion, edicion, activacion, desactivacion y bloqueo de cuentas.'],
    ['Roles y permisos',
     'Tres niveles de acceso (consulta, gestion, administracion) y matriz de '
     'permisos por modulo.'],
    ['Areas', 'Definicion de dependencias que segmentan el acceso a los documentos.'],
    ['Carga documental',
     'Registro de documentos con validacion de formato, peso, tipo real e '
     'integridad del archivo.'],
    ['Metadatos',
     'Titulo, codigo, descripcion, categoria, caso, area, autor, fecha, nivel de '
     'confidencialidad, palabras clave y etiquetas.'],
    ['Clasificacion',
     'Organizacion por categorias, casos o expedientes, areas y etiquetas.'],
    ['Procesamiento de texto',
     'Extraccion del contenido de archivos PDF, DOCX, XLSX y TXT para ampliar el '
     'alcance de la busqueda.'],
    ['Versionado', 'Conservacion del historial completo de archivos de cada documento.'],
    ['Busqueda',
     'Busqueda por palabras clave sobre metadatos y contenido, con puntuacion de '
     'coincidencia y visualizacion del fragmento encontrado.'],
    ['Filtros avanzados',
     'Filtrado por categoria, area, caso, estado, confidencialidad, etiqueta, '
     'responsable y rango de fechas.'],
    ['Historial de consultas',
     'Registro de cada busqueda realizada, con tiempo de respuesta y resultados '
     'obtenidos.'],
    ['Auditoria',
     'Bitacora de accesos, cargas, modificaciones, descargas y eliminaciones, con '
     'estado anterior y posterior.'],
    ['Respaldo', 'Generacion y descarga de copias de seguridad en formato SQL.'],
    ['Reportes', 'Indicadores de uso, inventario documental y exportacion a CSV.'],
    ['Control de integridad',
     'Verificacion mediante hash SHA-256 en cada descarga.']],
   [4.2, 12.0]))
C(('titulo', 2, '5.2. No incluye'))
C(('parrafo',
   'Los siguientes elementos quedan expresamente fuera del alcance de esta '
   'version:'))
C(('tabla', ['Elemento excluido', 'Justificacion'],
   [['Inteligencia artificial',
     'No se incorpora ningun modelo de lenguaje, red neuronal ni servicio de '
     'inferencia. El sistema fue redefinido para operar sin IA.'],
    ['Chat conversacional',
     'No existe una interfaz de dialogo. La consulta se realiza mediante un '
     'formulario de busqueda con palabras clave y filtros.'],
    ['Generacion automatica de respuestas',
     'El sistema no redacta ni sintetiza texto. Devuelve los documentos y '
     'fragmentos que coinciden con el termino buscado.'],
    ['Recuperacion semantica',
     'No se calculan embeddings ni se utiliza base de datos vectorial. La '
     'busqueda es lexica y determinista.'],
    ['Reconocimiento optico de caracteres (OCR)',
     'Los PDF escaneados como imagen no generan texto extraible. El documento '
     'sigue siendo consultable por sus metadatos.'],
    ['Correccion o interpretacion del contenido',
     'El sistema no corrige, resume ni interpreta el contenido documental.'],
    ['Toma de decisiones',
     'La aplicacion no sustituye el criterio profesional ni emite conceptos '
     'automaticos.'],
    ['Acceso a documentos no autorizados',
     'Por diseno, el sistema nunca expone documentos fuera del alcance del rol y '
     'el area del usuario.']],
   [5.0, 11.2]))
C(('aviso', 'Sobre la extraccion de texto',
   'La extraccion de texto no constituye un componente de inteligencia '
   'artificial. Es una operacion de lectura de formato: se descomprime el '
   'contenedor del archivo y se recuperan las cadenas de texto almacenadas. No '
   'hay interpretacion, traduccion ni analisis del contenido.', 'DBEAF7'))
C(('salto',))
