# -*- coding: utf-8 -*-
"""
=====================================================================
 MOTOR DE RENDERIZADO
 Convierte la estructura de contenido en documentos .docx y .pdf
=====================================================================
"""

import os
from docx import Document
from docx.shared import Pt, Cm, RGBColor
from docx.enum.text import WD_BREAK
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

AZUL = RGBColor(0x1B, 0x4F, 0x7F)
AZUL_CLARO = RGBColor(0x14, 0x39, 0x5C)
GRIS = RGBColor(0x5A, 0x64, 0x72)

NOMBRE_SISTEMA = 'Sistema Privado de Gestion y Consulta Documental'
VERSION_DOC = 'Version 2.0 - Sin componentes de inteligencia artificial'

COLORES_HEX = {
    'amarillo': 'FDEFD6',
    'azul': 'DBEAF7',
    'rojo': 'FBE0E0',
    'verde': 'D9F2E4',
}


# =====================================================================
#  RENDERIZADO A DOCX
# =====================================================================

def _sombrear(celda, color_hex):
    sombreado = OxmlElement('w:shd')
    sombreado.set(qn('w:val'), 'clear')
    sombreado.set(qn('w:color'), 'auto')
    sombreado.set(qn('w:fill'), color_hex)
    celda._tc.get_or_add_tcPr().append(sombreado)


def renderizar_docx(contenido, ruta):
    doc = Document()

    # --- Estilos base ---
    normal = doc.styles['Normal']
    normal.font.name = 'Calibri'
    normal.font.size = Pt(10.5)
    normal.paragraph_format.space_after = Pt(6)
    normal.paragraph_format.line_spacing = 1.15

    for nivel, tamano in ((1, 16), (2, 13), (3, 11.5)):
        h = doc.styles['Heading %d' % nivel]
        h.font.name = 'Calibri'
        h.font.color.rgb = AZUL
        h.font.bold = True
        h.font.size = Pt(tamano)

    for seccion in doc.sections:
        seccion.top_margin = Cm(2.2)
        seccion.bottom_margin = Cm(2.2)
        seccion.left_margin = Cm(2.5)
        seccion.right_margin = Cm(2.2)

    # --- Portada ---
    for _ in range(5):
        doc.add_paragraph()

    p = doc.add_paragraph()
    p.alignment = 1
    r = p.add_run('REQUERIMIENTOS')
    r.bold = True
    r.font.size = Pt(30)
    r.font.color.rgb = AZUL

    p = doc.add_paragraph()
    p.alignment = 1
    r = p.add_run(NOMBRE_SISTEMA)
    r.bold = True
    r.font.size = Pt(17)
    r.font.color.rgb = AZUL_CLARO

    doc.add_paragraph()

    p = doc.add_paragraph()
    p.alignment = 1
    r = p.add_run('Aplicacion web desarrollada con HTML, CSS, PHP y MySQL')
    r.italic = True
    r.font.size = Pt(12)
    r.font.color.rgb = GRIS

    p = doc.add_paragraph()
    p.alignment = 1
    r = p.add_run(VERSION_DOC)
    r.italic = True
    r.font.size = Pt(11)
    r.font.color.rgb = GRIS

    for _ in range(7):
        doc.add_paragraph()

    p = doc.add_paragraph()
    p.alignment = 1
    r = p.add_run('Documento de analisis y especificacion de requerimientos')
    r.font.size = Pt(10.5)
    r.font.color.rgb = GRIS

    doc.add_paragraph().add_run().add_break(WD_BREAK.PAGE)

    # --- Recorrido del contenido ---
    for numero, elemento in enumerate(contenido):
        tipo = elemento[0]

        try:
            if tipo == 'titulo':
                doc.add_heading(elemento[2], level=elemento[1])

            elif tipo == 'parrafo':
                doc.add_paragraph(elemento[1])

            elif tipo == 'vineta':
                p = doc.add_paragraph(elemento[1], style='List Bullet')
                p.paragraph_format.left_indent = Cm(0.75)

            elif tipo == 'numerada':
                doc.add_paragraph(elemento[1], style='List Number')

            elif tipo == 'contenido':
                p = doc.add_paragraph(elemento[1])
                p.paragraph_format.left_indent = Cm(0.8)
                p.paragraph_format.space_after = Pt(2)

            elif tipo == 'tabla':
                encabezados, filas, anchos = elemento[1], elemento[2], elemento[3]
                t = doc.add_table(rows=1, cols=len(encabezados))
                t.style = 'Table Grid'
                t.alignment = WD_TABLE_ALIGNMENT.CENTER

                celdas_enc = t.rows[0].cells
                for i, texto in enumerate(encabezados):
                    celdas_enc[i].text = ''
                    run = celdas_enc[i].paragraphs[0].add_run(str(texto))
                    run.bold = True
                    run.font.size = Pt(9.5)
                    run.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)
                    _sombrear(celdas_enc[i], '1B4F7F')

                for indice, fila in enumerate(filas):
                    celdas = t.add_row().cells
                    for i, valor in enumerate(fila):
                        celdas[i].text = ''
                        run = celdas[i].paragraphs[0].add_run(str(valor))
                        run.font.size = Pt(9)
                        if indice % 2 == 1:
                            _sombrear(celdas[i], 'F2F7FC')

                for fila in t.rows:
                    for i, ancho in enumerate(anchos):
                        if i < len(fila.cells):
                            fila.cells[i].width = Cm(ancho)

                doc.add_paragraph()

            elif tipo == 'aviso':
                color = elemento[3] if len(elemento) > 3 else 'amarillo'
                hex_color = COLORES_HEX.get(color, color)
                t = doc.add_table(rows=1, cols=1)
                t.style = 'Table Grid'
                celda = t.rows[0].cells[0]
                _sombrear(celda, hex_color)

                celda.text = ''
                run = celda.paragraphs[0].add_run(elemento[1])
                run.bold = True
                run.font.size = Pt(10)
                run.font.color.rgb = RGBColor(0x0D, 0x21, 0x37)

                run2 = celda.add_paragraph().add_run(elemento[2])
                run2.font.size = Pt(9.5)
                run2.font.color.rgb = GRIS

                doc.add_paragraph()

            elif tipo == 'salto':
                doc.add_paragraph().add_run().add_break(WD_BREAK.PAGE)

            elif tipo in ('portada',):
                pass  # El renderizador ya construyo la portada

            elif tipo == 'fin':
                doc.add_paragraph()
                p = doc.add_paragraph()
                p.alignment = 1
                r = p.add_run('- Fin del documento -')
                r.italic = True
                r.font.color.rgb = GRIS
                r.font.size = Pt(10)

            else:
                raise ValueError('Tipo de elemento desconocido: %r' % (tipo,))

        except Exception as error:
            raise ValueError(
                'Error en el elemento %d de tipo "%s": %s'
                % (numero, tipo, error))

    doc.save(ruta)
    return ruta


# =====================================================================
#  RENDERIZADO A PDF
# =====================================================================

def renderizar_pdf(contenido, ruta):
    from reportlab.lib.pagesizes import A4
    from reportlab.lib.styles import ParagraphStyle
    from reportlab.lib.units import cm
    from reportlab.lib import colors
    from reportlab.platypus import (
        SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak
    )
    from reportlab.lib.enums import TA_CENTER, TA_JUSTIFY

    AZUL_PDF = colors.HexColor('#1B4F7F')
    AZUL_OSCURO_PDF = colors.HexColor('#0D2137')
    GRIS_PDF = colors.HexColor('#5A6472')
    BORDE = colors.HexColor('#D8DEE7')

    estilos = {
        'h1': ParagraphStyle('h1', fontName='Helvetica-Bold', fontSize=15,
                             textColor=AZUL_PDF, spaceBefore=14, spaceAfter=8,
                             leading=19),
        'h2': ParagraphStyle('h2', fontName='Helvetica-Bold', fontSize=12,
                             textColor=AZUL_PDF, spaceBefore=11, spaceAfter=6,
                             leading=15),
        'cuerpo': ParagraphStyle('cuerpo', fontName='Helvetica', fontSize=9.5,
                                 textColor=colors.HexColor('#1F2733'),
                                 alignment=TA_JUSTIFY, spaceAfter=6, leading=13.5),
        'vineta': ParagraphStyle('vineta', fontName='Helvetica', fontSize=9.5,
                                 leftIndent=16, bulletIndent=6, spaceAfter=3,
                                 leading=13),
        'numerada': ParagraphStyle('numerada', fontName='Helvetica', fontSize=9.5,
                                   leftIndent=16, bulletIndent=6, spaceAfter=3,
                                   leading=13),
        'contenido': ParagraphStyle('contenido', fontName='Helvetica', fontSize=9.5,
                                    leftIndent=18, spaceAfter=2, leading=13),
        'celda': ParagraphStyle('celda', fontName='Helvetica', fontSize=8.2,
                                leading=11, textColor=colors.HexColor('#1F2733')),
        'celda_enc': ParagraphStyle('celda_enc', fontName='Helvetica-Bold',
                                    fontSize=8.5, leading=11,
                                    textColor=colors.white),
        'aviso_titulo': ParagraphStyle('aviso_titulo', fontName='Helvetica-Bold',
                                       fontSize=9.5, leading=12,
                                       textColor=AZUL_OSCURO_PDF),
        'aviso_texto': ParagraphStyle('aviso_texto', fontName='Helvetica',
                                      fontSize=8.8, leading=12,
                                      textColor=GRIS_PDF, alignment=TA_JUSTIFY),
        'portada_titulo': ParagraphStyle('portada_titulo', fontName='Helvetica-Bold',
                                         fontSize=28, leading=34,
                                         textColor=AZUL_PDF, alignment=TA_CENTER),
        'portada_sub': ParagraphStyle('portada_sub', fontName='Helvetica-Bold',
                                      fontSize=15, leading=20,
                                      textColor=colors.HexColor('#14395C'),
                                      alignment=TA_CENTER),
        'portada_it': ParagraphStyle('portada_it', fontName='Helvetica-Oblique',
                                     fontSize=10.5, leading=15,
                                     textColor=GRIS_PDF, alignment=TA_CENTER),
    }

    def esc(texto):
        """Escapa el texto para el marcado de ReportLab."""
        return (str(texto).replace('&', '&amp;')
                          .replace('<', '&lt;')
                          .replace('>', '&gt;'))

    historia = []

    # --- Portada ---
    historia.append(Spacer(1, 5 * cm))
    historia.append(Paragraph('REQUERIMIENTOS', estilos['portada_titulo']))
    historia.append(Spacer(1, 0.7 * cm))
    historia.append(Paragraph(esc(NOMBRE_SISTEMA), estilos['portada_sub']))
    historia.append(Spacer(1, 1.6 * cm))
    historia.append(Paragraph('Aplicacion web desarrollada con HTML, CSS, PHP y MySQL',
                              estilos['portada_it']))
    historia.append(Spacer(1, 0.3 * cm))
    historia.append(Paragraph(esc(VERSION_DOC), estilos['portada_it']))
    historia.append(Spacer(1, 4 * cm))
    historia.append(Paragraph('Documento de analisis y especificacion de requerimientos',
                              estilos['portada_it']))
    historia.append(PageBreak())

    # --- Recorrido ---
    contador_numerada = 0

    for elemento in contenido:
        tipo = elemento[0]

        if tipo == 'titulo':
            historia.append(Paragraph(esc(elemento[2]), estilos['h%d' % elemento[1]]))
            contador_numerada = 0

        elif tipo == 'parrafo':
            historia.append(Paragraph(esc(elemento[1]), estilos['cuerpo']))

        elif tipo == 'vineta':
            historia.append(Paragraph('&bull;&nbsp;&nbsp;' + esc(elemento[1]),
                                      estilos['vineta']))

        elif tipo == 'numerada':
            contador_numerada += 1
            historia.append(Paragraph('%d.&nbsp;&nbsp;%s' % (contador_numerada,
                                                             esc(elemento[1])),
                                      estilos['numerada']))

        elif tipo == 'contenido':
            historia.append(Paragraph(esc(elemento[1]), estilos['contenido']))

        elif tipo == 'tabla':
            encabezados, filas, anchos = elemento[1], elemento[2], elemento[3]

            datos = [[Paragraph(esc(h), estilos['celda_enc']) for h in encabezados]]
            for fila in filas:
                datos.append([Paragraph(esc(v), estilos['celda']) for v in fila])

            # Los anchos se convierten de centimetros manteniendo la proporcion
            ancho_disponible = 16.4 * cm
            total = sum(anchos)
            anchos_pt = [(a / total) * ancho_disponible for a in anchos]

            tabla = Table(datos, colWidths=anchos_pt, repeatRows=1)
            tabla.setStyle(TableStyle([
                ('BACKGROUND', (0, 0), (-1, 0), AZUL_PDF),
                ('TEXTCOLOR', (0, 0), (-1, 0), colors.white),
                ('VALIGN', (0, 0), (-1, -1), 'TOP'),
                ('GRID', (0, 0), (-1, -1), 0.4, BORDE),
                ('LEFTPADDING', (0, 0), (-1, -1), 5),
                ('RIGHTPADDING', (0, 0), (-1, -1), 5),
                ('TOPPADDING', (0, 0), (-1, -1), 4),
                ('BOTTOMPADDING', (0, 0), (-1, -1), 4),
            ]))

            # Filas alternas sombreadas
            for i in range(1, len(datos)):
                if i % 2 == 0:
                    tabla.setStyle(TableStyle([
                        ('BACKGROUND', (0, i), (-1, i), colors.HexColor('#F2F7FC'))
                    ]))

            historia.append(tabla)
            historia.append(Spacer(1, 0.4 * cm))

        elif tipo == 'aviso':
            color = elemento[3] if len(elemento) > 3 else 'amarillo'
            hex_color = COLORES_HEX.get(color, color)

            interior = Table(
                [[Paragraph(esc(elemento[1]), estilos['aviso_titulo'])],
                 [Paragraph(esc(elemento[2]), estilos['aviso_texto'])]],
                colWidths=[16.4 * cm]
            )
            interior.setStyle(TableStyle([
                ('BACKGROUND', (0, 0), (-1, -1), colors.HexColor('#' + hex_color)),
                ('LEFTPADDING', (0, 0), (-1, -1), 8),
                ('RIGHTPADDING', (0, 0), (-1, -1), 8),
                ('TOPPADDING', (0, 0), (0, 0), 7),
                ('BOTTOMPADDING', (0, -1), (-1, -1), 7),
                ('BOTTOMPADDING', (0, 0), (0, 0), 2),
                ('BOX', (0, 0), (-1, -1), 0.4, BORDE),
            ]))
            historia.append(interior)
            historia.append(Spacer(1, 0.45 * cm))

        elif tipo == 'salto':
            historia.append(PageBreak())

        elif tipo == 'portada':
            pass  # La portada ya se construyo antes del recorrido

        elif tipo == 'fin':
            historia.append(Spacer(1, 0.5 * cm))
            historia.append(Paragraph('<i>- Fin del documento -</i>',
                                      estilos['portada_it']))

    # --- Documento con numeracion de paginas ---
    def decorar_pagina(canvas, documento):
        canvas.saveState()
        ancho, alto = A4

        # Encabezado (a partir de la segunda pagina)
        if documento.page > 1:
            canvas.setFont('Helvetica', 7.5)
            canvas.setFillColor(GRIS_PDF)
            canvas.drawString(2.2 * cm, alto - 1.3 * cm, NOMBRE_SISTEMA)
            canvas.drawRightString(ancho - 2.2 * cm, alto - 1.3 * cm,
                                   'Requerimientos v2.0')
            canvas.setStrokeColor(BORDE)
            canvas.setLineWidth(0.4)
            canvas.line(2.2 * cm, alto - 1.6 * cm, ancho - 2.2 * cm, alto - 1.6 * cm)

        # Pie de pagina
        canvas.setFont('Helvetica', 7.5)
        canvas.setFillColor(GRIS_PDF)
        canvas.drawCentredString(ancho / 2, 1.3 * cm,
                                 'Pagina %d' % documento.page)
        canvas.setStrokeColor(BORDE)
        canvas.setLineWidth(0.4)
        canvas.line(2.2 * cm, 1.6 * cm, ancho - 2.2 * cm, 1.6 * cm)

        canvas.restoreState()

    documento = SimpleDocTemplate(
        ruta, pagesize=A4,
        leftMargin=2.2 * cm, rightMargin=2.2 * cm,
        topMargin=2.0 * cm, bottomMargin=2.0 * cm,
        title='Requerimientos - %s' % NOMBRE_SISTEMA,
        author='Documento de analisis',
        subject='Requerimientos del sistema',
    )

    documento.build(historia, onFirstPage=decorar_pagina,
                    onLaterPages=decorar_pagina)
    return ruta
