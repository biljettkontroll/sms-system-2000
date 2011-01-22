/*

  $Id$

  X G N O K I I

  A Linux/Unix GUI for the mobile phones.

  This file is part of gnokii.

  Gnokii is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Gnokii is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with gnokii; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

  Copyright (C) 1999 Pavel Janik ml., Hugh Blemings
  & 1999-2005 Jan Derfinak
  Copyright (C) 2002-2003 Pawel Kot
  Copyright (C) 2002      BORBELY Zoltan, Markus Plail

*/

#include "config.h"

#include <stdio.h>
#include <stdlib.h>
#include <pthread.h>
#include <gtk/gtk.h>
#include "xgnokii_contacts.h"
#include "xgnokii.h"
#include "xgnokii_lowlevel.h"
#include "xgnokii_common.h"
#include "xgnokii_speed.h"
#include "xpm/Read.xpm"
#include "xpm/Send.xpm"
#include "xpm/Open.xpm"
#include "xpm/Save.xpm"
#include "xpm/Edit.xpm"
#include "xpm/quest.xpm"


static GtkWidget *GUI_SpeedDialWindow;
static ErrorDialog errorDialog = { NULL, NULL };
static InfoDialog infoDialog = { NULL, NULL };
static ExportDialogData exportDialogData = { NULL };
static GtkWidget *clist;
static QuestMark questMark;
static gint selectedKey;
static bool speedDialInitialized;


static void CloseSpeedDial(GtkWidget * w, gpointer data)
{
	gtk_widget_hide(GUI_SpeedDialWindow);
}


static inline void DestroyCListData(gpointer data)
{
	if (data)
		g_free((D_SpeedDial *) data);
}


static void DeleteSelectContactDialog(GtkWidget * widget, GdkEvent * event,
				      SelectContactData * data)
{
	gtk_widget_destroy(GTK_WIDGET(data->clist));
	gtk_widget_destroy(GTK_WIDGET(data->clistScrolledWindow));
	gtk_widget_destroy(GTK_WIDGET(widget));
}


static void CancelSelectContactDialog(GtkWidget * widget, SelectContactData * data)
{
	gtk_widget_destroy(GTK_WIDGET(data->clist));
	gtk_widget_destroy(GTK_WIDGET(data->clistScrolledWindow));
	gtk_widget_destroy(GTK_WIDGET(data->dialog));
}


static void OkSelectContactDialog(GtkWidget * widget, SelectContactData * data)
{
	GList *sel;
	PhonebookEntry *pbEntry;
	gchar *key;

	if ((sel = GTK_CLIST(data->clist)->selection) != NULL) {
		D_SpeedDial *d = (D_SpeedDial *) g_malloc(sizeof(D_SpeedDial));

		gtk_clist_freeze(GTK_CLIST(clist));

		pbEntry = gtk_clist_get_row_data(GTK_CLIST(data->clist),
						 GPOINTER_TO_INT(sel->data));

		gtk_clist_get_text(GTK_CLIST(clist), selectedKey, 0, &key);

		gtk_clist_set_text(GTK_CLIST(clist), selectedKey, 1, pbEntry->entry.name);
		gtk_clist_set_text(GTK_CLIST(clist), selectedKey, 2, pbEntry->entry.number);

		d->entry.number = *key - '0';
		d->entry.memory_type = pbEntry->entry.memory_type + 2;
		d->entry.location = pbEntry->entry.location;

		gtk_clist_set_row_data_full(GTK_CLIST(clist), selectedKey,
					    (gpointer) d, DestroyCListData);

		gtk_clist_sort(GTK_CLIST(clist));
		gtk_clist_thaw(GTK_CLIST(clist));
	}

	gtk_widget_destroy(GTK_WIDGET(data->clist));
	gtk_widget_destroy(GTK_WIDGET(data->clistScrolledWindow));
	gtk_widget_destroy(GTK_WIDGET(data->dialog));
}

static void ShowSelectContactsDialog(void)
{
	SelectContactData *r;

	if (!GUI_ContactsIsIntialized())
		GUI_ReadContacts();

	if ((r = GUI_SelectContactDialog()) == NULL)
		return;

	gtk_signal_connect(GTK_OBJECT(r->dialog), "delete_event",
			   GTK_SIGNAL_FUNC(DeleteSelectContactDialog), (gpointer) r);

	gtk_signal_connect(GTK_OBJECT(r->okButton), "clicked",
			   GTK_SIGNAL_FUNC(OkSelectContactDialog), (gpointer) r);
	gtk_signal_connect(GTK_OBJECT(r->cancelButton), "clicked",
			   GTK_SIGNAL_FUNC(CancelSelectContactDialog), (gpointer) r);
}


static inline void EditKey(void)
{
	GList *sel;

	if ((sel = GTK_CLIST(clist)->selection) != NULL) {
		selectedKey = GPOINTER_TO_INT(sel->data);
		ShowSelectContactsDialog();
	}
}


static inline void ClickEntry(GtkWidget * clist,
			      gint row, gint column, GdkEventButton * event, gpointer data)
{
	if (event && event->type == GDK_2BUTTON_PRESS) {
		selectedKey = row;
		ShowSelectContactsDialog();
	}
}


static void ReadSpeedDial(void)
{
	PhonebookEntry *pbEntry;
	D_SpeedDial *d;
	PhoneEvent *e;
	gchar *row[3];
	gchar buf[2] = " ";
	gint location;
	register gint i, row_i = 0;


	if (!GUI_ContactsIsIntialized())
		GUI_ReadContacts();

	gtk_label_set_text(GTK_LABEL(infoDialog.text), _("Reading data ..."));
	gtk_widget_show_now(infoDialog.dialog);
	GUI_Refresh();

	gtk_clist_freeze(GTK_CLIST(clist));
	gtk_clist_clear(GTK_CLIST(clist));

	for (i = 2; i < 10; i++) {
		if ((d = (D_SpeedDial *) g_malloc(sizeof(D_SpeedDial))) == NULL) {
			g_print(_("Error: %s: line %d: Can't allocate memory!\n"), __FILE__, __LINE__);
			return;
		}
		memset(d, 0, sizeof(D_SpeedDial));
		d->entry.number = i;
		if ((e = (PhoneEvent *) g_malloc(sizeof(PhoneEvent))) == NULL) {
			g_print(_("Error: %s: line %d: Can't allocate memory!\n"), __FILE__, __LINE__);
			g_free(d);
			return;
		}
		e->event = Event_GetSpeedDial;
		e->data = d;
		GUI_InsertEvent(e);
		pthread_mutex_lock(&speedDialMutex);
		pthread_cond_wait(&speedDialCond, &speedDialMutex);
		pthread_mutex_unlock(&speedDialMutex);
		if (d->status != GN_ERR_NONE) {
			g_print(_("Cannot read speed dial key %d!\n"), i);
			*buf = i + '0';
			row[0] = buf;
			row[1] = '\0';
			row[2] = '\0';
		} else {
			if (d->entry.location == 0)
				location = i;
			else
				location = d->entry.location;
			if ((pbEntry = GUI_GetEntry(d->entry.memory_type, location)) == NULL) {
				g_free(d);
				continue;
			}
			*buf = i + '0';
			row[0] = buf;
			row[1] = pbEntry->entry.name;
			row[2] = pbEntry->entry.number;

		}
		gtk_clist_append(GTK_CLIST(clist), row);
		gtk_clist_set_row_data_full(GTK_CLIST(clist), row_i++,
					    (gpointer) d, DestroyCListData);
		/*
		GUI_Refresh ();
		*/
	}
	gtk_widget_hide(infoDialog.dialog);
		
	gtk_clist_sort(GTK_CLIST(clist));
	gtk_clist_thaw(GTK_CLIST(clist));
	speedDialInitialized = TRUE;
}


static void SaveSpeedDial(void)
{
	//gchar buf[80];
	D_SpeedDial *d;
	PhoneEvent *e;
	register gint i;

	if (speedDialInitialized) {
		for (i = 1; i < 10; i++) {
			if ((d = (D_SpeedDial *) gtk_clist_get_row_data(GTK_CLIST(clist), i - 1))) {
				gn_log_xdebug("location: %i\n", d->entry.location);
				if (d->entry.location == 0)
					continue;
				if ((e = (PhoneEvent *) g_malloc(sizeof(PhoneEvent))) == NULL) {
					g_print(_("Error: %s: line %d: Can't allocate memory!\n"), __FILE__, __LINE__);
					return;
				}

				e->event = Event_SendSpeedDial;
				e->data = d;
				GUI_InsertEvent(e);
				pthread_mutex_lock (&speedDialMutex);
				pthread_cond_wait (&speedDialCond, &speedDialMutex);
				pthread_mutex_unlock (&speedDialMutex);

				if (d->status != GN_ERR_NONE) {
					g_print(_("Error writing speed dial for key %d!\n"), d->entry.number);
					/*
					  gtk_label_set_text (GTK_LABEL (errorDialog.text), buf);
					  gtk_widget_show (errorDialog.dialog);
					*/
				}
				/*
				GUI_Refresh ();
				*/
			}
		}
	}
}

static bool ParseLine(D_SpeedDial * d, gchar * buf)
{
	gchar **strings = g_strsplit(buf, ";", 3);

	d->entry.number = *strings[0] - '0';
	if (d->entry.number < 1 || d->entry.number > 9) {
		g_strfreev(strings);
		return FALSE;
	}

	d->entry.memory_type = *strings[1] - '0';
	if (d->entry.memory_type < 2 || d->entry.memory_type > 3) {
		g_strfreev(strings);
		return FALSE;
	}

	d->entry.location = atoi(strings[2]);
	if (d->entry.location == INT_MAX || d->entry.location < 0) {
		g_strfreev(strings);
		return FALSE;
	}

	g_strfreev(strings);
	return TRUE;
}


static void OkImportDialog(GtkWidget * w, GtkFileSelection * fs)
{
	FILE *f;
	D_SpeedDial *d;
	PhonebookEntry *pbEntry;
	gchar buf[IO_BUF_LEN];
	gchar *row[3];
	gchar *fileName;
	gint location;
	register gint i, row_i = 0;

	fileName = (gchar *) gtk_file_selection_get_filename(GTK_FILE_SELECTION(fs));
	gtk_widget_hide(GTK_WIDGET(fs));

	if ((f = fopen(fileName, "r")) == NULL) {
		g_snprintf(buf, IO_BUF_LEN, _("Can't open file %s for reading!\n"), fileName);
		gtk_label_set_text(GTK_LABEL(errorDialog.text), buf);
		gtk_widget_show(errorDialog.dialog);
		return;
	}

	if (!GUI_ContactsIsIntialized())
		GUI_ReadContacts();

	gtk_clist_freeze(GTK_CLIST(clist));
	gtk_clist_clear(GTK_CLIST(clist));
	speedDialInitialized = FALSE;

	i = 0;
	while (fgets(buf, IO_BUF_LEN, f) && i++ < 9) {
		if ((d = (D_SpeedDial *) g_malloc(sizeof(D_SpeedDial))) == NULL) {
			g_print(_("Error: %s: line %d: Can't allocate memory!\n"), __FILE__, __LINE__);
			gtk_clist_clear(GTK_CLIST(clist));
			gtk_clist_sort(GTK_CLIST(clist));
			gtk_clist_thaw(GTK_CLIST(clist));
			return;
		}
		if (ParseLine(d, buf)) {
			if (d->entry.number != i) {
				g_free(d);
				gtk_clist_clear(GTK_CLIST(clist));
				gtk_label_set_text(GTK_LABEL(errorDialog.text),
						   _("Error reading file!"));
				gtk_widget_show(errorDialog.dialog);
				gtk_clist_sort(GTK_CLIST(clist));
				gtk_clist_thaw(GTK_CLIST(clist));
				return;
			}
			if (d->entry.location == 0)
				location = i;
			else
				location = d->entry.location;
			if ((pbEntry = GUI_GetEntry(d->entry.memory_type - 2, location)) == NULL) {
				g_free(d);
				continue;
			}
			*buf = i + '0';
			*(buf + 1) = '\0';
			row[0] = buf;
			row[1] = pbEntry->entry.name;
			row[2] = pbEntry->entry.number;
			gtk_clist_append(GTK_CLIST(clist), row);
			gtk_clist_set_row_data_full(GTK_CLIST(clist), row_i++,
						    (gpointer) d, DestroyCListData);
		} else {
			g_free(d);
			gtk_clist_clear(GTK_CLIST(clist));
			gtk_label_set_text(GTK_LABEL(errorDialog.text), _("Error reading file!"));
			gtk_widget_show(errorDialog.dialog);
			gtk_clist_sort(GTK_CLIST(clist));
			gtk_clist_thaw(GTK_CLIST(clist));
			return;
		}
	}

	gtk_clist_sort(GTK_CLIST(clist));
	gtk_clist_thaw(GTK_CLIST(clist));
	speedDialInitialized = TRUE;
}


static void ImportSpeedDial(void)
{
	static GtkWidget *fileDialog = NULL;

	if (fileDialog == NULL) {
		fileDialog = gtk_file_selection_new(_("Import from file"));
		gtk_signal_connect(GTK_OBJECT(fileDialog), "delete_event",
				   GTK_SIGNAL_FUNC(DeleteEvent), NULL);
		gtk_signal_connect(GTK_OBJECT(GTK_FILE_SELECTION(fileDialog)->ok_button),
				   "clicked", GTK_SIGNAL_FUNC(OkImportDialog),
				   (gpointer) fileDialog);
		gtk_signal_connect(GTK_OBJECT(GTK_FILE_SELECTION(fileDialog)->cancel_button),
				   "clicked", GTK_SIGNAL_FUNC(CancelDialog), (gpointer) fileDialog);
	}

	gtk_widget_show(fileDialog);
}


static void ExportSpeedDialMain(gchar * name)
{
	FILE *f;
	D_SpeedDial *d;
	gchar buf[IO_BUF_LEN];
	register gint i;

	if ((f = fopen(name, "w")) == NULL) {
		g_snprintf(buf, IO_BUF_LEN, _("Can't open file %s for writing!\n"), name);
		gtk_label_set_text(GTK_LABEL(errorDialog.text), buf);
		gtk_widget_show(errorDialog.dialog);
		return;
	}

	for (i = 1; i < 10; i++) {
		if ((d = (D_SpeedDial *) gtk_clist_get_row_data(GTK_CLIST(clist), i - 1))) {
			snprintf(buf, sizeof(buf), "%d;%d;%d;", d->entry.number, d->entry.memory_type,
				d->entry.location);
			fprintf(f, "%s\n", buf);
		}
	}

	fclose(f);
}


static void YesExportDialog(GtkWidget * w, gpointer data)
{
	gtk_widget_hide(GTK_WIDGET(data));
	ExportSpeedDialMain(exportDialogData.fileName);
}


static void OkExportDialog(GtkWidget * w, GtkFileSelection * fs)
{
	static YesNoDialog dialog = { NULL, NULL };
	FILE *f;
	gchar err[255];


	exportDialogData.fileName = (gchar *) gtk_file_selection_get_filename(GTK_FILE_SELECTION(fs));
	gtk_widget_hide(GTK_WIDGET(fs));

	if ((f = fopen(exportDialogData.fileName, "r")) != NULL) {
		fclose(f);
		if (dialog.dialog == NULL) {
			CreateYesNoDialog(&dialog, (GtkSignalFunc) YesExportDialog, (GtkSignalFunc) CancelDialog,
					  GUI_SpeedDialWindow);
			gtk_window_set_title(GTK_WINDOW(dialog.dialog), _("Overwrite file?"));
			g_snprintf(err, 255, _("File %s already exists.\nOverwrite?"),
				   exportDialogData.fileName);
			gtk_label_set_text(GTK_LABEL(dialog.text), err);
		}
		gtk_widget_show(dialog.dialog);
	} else
		ExportSpeedDialMain(exportDialogData.fileName);
}


static void ExportSpeedDial(void)
{
	static GtkWidget *fileDialog = NULL;

	if (speedDialInitialized) {
		if (fileDialog == NULL) {
			fileDialog = gtk_file_selection_new(_("Export to file"));
			gtk_signal_connect(GTK_OBJECT(fileDialog), "delete_event",
					   GTK_SIGNAL_FUNC(DeleteEvent), NULL);
			gtk_signal_connect(GTK_OBJECT(GTK_FILE_SELECTION(fileDialog)->ok_button),
					   "clicked", GTK_SIGNAL_FUNC(OkExportDialog),
					   (gpointer) fileDialog);
			gtk_signal_connect(GTK_OBJECT
					   (GTK_FILE_SELECTION(fileDialog)->cancel_button),
					   "clicked", GTK_SIGNAL_FUNC(CancelDialog),
					   (gpointer) fileDialog);
		}

		gtk_widget_show(fileDialog);
	}
}


inline void GUI_ShowSpeedDial(void)
{
	ReadSpeedDial();
	gtk_window_present(GTK_WINDOW(GUI_SpeedDialWindow));
}


static GtkItemFactoryEntry menu_items[] = {
	{NULL, NULL, NULL, 0, "<Branch>"},
	{NULL, "<control>R", ReadSpeedDial, 0, NULL},
	{NULL, "<control>S", SaveSpeedDial, 0, NULL},
	{NULL, NULL, NULL, 0, "<Separator>"},
	{NULL, "<control>I", ImportSpeedDial, 0, NULL},
	{NULL, "<control>E", ExportSpeedDial, 0, NULL},
	{NULL, NULL, NULL, 0, "<Separator>"},
	{NULL, "<control>W", CloseSpeedDial, 0, NULL},
	{NULL, NULL, NULL, 0, "<Branch>"},
	{NULL, NULL, EditKey, 0, NULL},
	{NULL, NULL, NULL, 0, "<LastBranch>"},
	{NULL, NULL, GUI_ShowAbout, 0, NULL},
};


static void InitMainMenu(void)
{
	menu_items[0].path = _("/_File");
	menu_items[1].path = _("/File/_Read from phone");
	menu_items[2].path = _("/File/_Save to phone");
	menu_items[3].path = _("/File/Sep1");
	menu_items[4].path = _("/File/_Import from file");
	menu_items[5].path = _("/File/_Export to file");
	menu_items[6].path = _("/File/Sep2");
	menu_items[7].path = _("/File/_Close");
	menu_items[8].path = _("/_Edit");
	menu_items[9].path = _("/Edit/_Edit");
	menu_items[10].path = _("/_Help");
	menu_items[11].path = _("/Help/_About");
}


void GUI_CreateSpeedDialWindow(void)
{
	int nmenu_items = sizeof(menu_items) / sizeof(menu_items[0]);
	GtkItemFactory *item_factory;
	GtkAccelGroup *accel_group;
	SortColumn *sColumn;
	GtkWidget *menubar;
	GtkWidget *main_vbox;
	GtkWidget *toolbar;
	GtkWidget *clistScrolledWindow;
	register gint i;
	gchar *titles[3] = { _("Key"), _("Name"), _("Number") };


	InitMainMenu();
	GUI_SpeedDialWindow = gtk_window_new(GTK_WINDOW_TOPLEVEL);
	gtk_window_set_wmclass(GTK_WINDOW(GUI_SpeedDialWindow), "SpeedDialWindow", "Xgnokii");
	gtk_window_set_title(GTK_WINDOW(GUI_SpeedDialWindow), _("Speed Dial"));
	gtk_widget_set_usize(GTK_WIDGET(GUI_SpeedDialWindow), 350, 220);
	//gtk_container_set_border_width (GTK_CONTAINER (GUI_SpeedDialWindow), 10);
	gtk_signal_connect(GTK_OBJECT(GUI_SpeedDialWindow), "delete_event",
			   GTK_SIGNAL_FUNC(DeleteEvent), NULL);
	gtk_widget_realize(GUI_SpeedDialWindow);

	accel_group = gtk_accel_group_new();
	item_factory = gtk_item_factory_new(GTK_TYPE_MENU_BAR, "<main>", accel_group);

	gtk_item_factory_create_items(item_factory, nmenu_items, menu_items, NULL);

	gtk_window_add_accel_group(GTK_WINDOW(GUI_SpeedDialWindow), accel_group);

	/* Finally, return the actual menu bar created by the item factory. */
	menubar = gtk_item_factory_get_widget(item_factory, "<main>");

	main_vbox = gtk_vbox_new(FALSE, 1);
	gtk_container_border_width(GTK_CONTAINER(main_vbox), 1);
	gtk_container_add(GTK_CONTAINER(GUI_SpeedDialWindow), main_vbox);
	gtk_widget_show(main_vbox);

	gtk_box_pack_start(GTK_BOX(main_vbox), menubar, FALSE, FALSE, 0);
	gtk_widget_show(menubar);

	/* Create the toolbar */

	toolbar = gtk_toolbar_new();
	gtk_toolbar_set_style(GTK_TOOLBAR(toolbar), GTK_TOOLBAR_ICONS);
	gtk_toolbar_set_orientation(GTK_TOOLBAR(toolbar), GTK_ORIENTATION_HORIZONTAL);

	gtk_toolbar_append_item(GTK_TOOLBAR(toolbar), NULL, _("Read from phone"), NULL,
				NewPixmap(Read_xpm, GUI_SpeedDialWindow->window,
					  &GUI_SpeedDialWindow->style->bg[GTK_STATE_NORMAL]),
				(GtkSignalFunc) ReadSpeedDial, NULL);
	gtk_toolbar_append_item(GTK_TOOLBAR(toolbar), NULL, _("Save to phone"), NULL,
				NewPixmap(Send_xpm, GUI_SpeedDialWindow->window,
					  &GUI_SpeedDialWindow->style->bg[GTK_STATE_NORMAL]),
				(GtkSignalFunc) SaveSpeedDial, NULL);

	gtk_toolbar_append_space(GTK_TOOLBAR(toolbar));

	gtk_toolbar_append_item(GTK_TOOLBAR(toolbar), NULL, _("Import from file"), NULL,
				NewPixmap(Open_xpm, GUI_SpeedDialWindow->window,
					  &GUI_SpeedDialWindow->style->bg[GTK_STATE_NORMAL]),
				(GtkSignalFunc) ImportSpeedDial, NULL);
	gtk_toolbar_append_item(GTK_TOOLBAR(toolbar), NULL, _("Export to file"), NULL,
				NewPixmap(Save_xpm, GUI_SpeedDialWindow->window,
					  &GUI_SpeedDialWindow->style->bg[GTK_STATE_NORMAL]),
				(GtkSignalFunc) ExportSpeedDial, NULL);

	gtk_toolbar_append_space(GTK_TOOLBAR(toolbar));

	gtk_toolbar_append_item(GTK_TOOLBAR(toolbar), NULL, _("Edit entry"), NULL,
				NewPixmap(Edit_xpm, GUI_SpeedDialWindow->window,
					  &GUI_SpeedDialWindow->style->bg[GTK_STATE_NORMAL]),
				(GtkSignalFunc) EditKey, NULL);

	gtk_box_pack_start(GTK_BOX(main_vbox), toolbar, FALSE, FALSE, 0);
	gtk_widget_show(toolbar);

	clist = gtk_clist_new_with_titles(3, titles);
	gtk_clist_set_shadow_type(GTK_CLIST(clist), GTK_SHADOW_OUT);
//  gtk_clist_set_compare_func (GTK_CLIST (clist), CListCompareFunc);
	gtk_clist_set_sort_column(GTK_CLIST(clist), 0);
	gtk_clist_set_sort_type(GTK_CLIST(clist), GTK_SORT_ASCENDING);
	gtk_clist_set_auto_sort(GTK_CLIST(clist), FALSE);
	//gtk_clist_set_selection_mode (GTK_CLIST (clist), GTK_SELECTION_EXTENDED);

	gtk_clist_set_column_width(GTK_CLIST(clist), 1, 150);
	gtk_clist_set_column_width(GTK_CLIST(clist), 2, 115);
	gtk_clist_set_column_justification(GTK_CLIST(clist), 0, GTK_JUSTIFY_CENTER);
//  gtk_clist_set_column_visibility (GTK_CLIST (clist), 3, xgnokiiConfig.callerGroupsSupported);

	for (i = 0; i < 3; i++) {
		if ((sColumn = g_malloc(sizeof(SortColumn))) == NULL) {
			g_print(_("Error: %s: line %d: Can't allocate memory!\n"), __FILE__, __LINE__);
			gtk_main_quit();
		}
		sColumn->clist = clist;
		sColumn->column = i;
		gtk_signal_connect(GTK_OBJECT(GTK_CLIST(clist)->column[i].button), "clicked",
				   GTK_SIGNAL_FUNC(SetSortColumn), (gpointer) sColumn);
	}

	gtk_signal_connect(GTK_OBJECT(clist), "select_row", GTK_SIGNAL_FUNC(ClickEntry), NULL);

	clistScrolledWindow = gtk_scrolled_window_new(NULL, NULL);
	gtk_container_add(GTK_CONTAINER(clistScrolledWindow), clist);
	gtk_scrolled_window_set_policy(GTK_SCROLLED_WINDOW(clistScrolledWindow),
				       GTK_POLICY_AUTOMATIC, GTK_POLICY_AUTOMATIC);
	gtk_box_pack_start(GTK_BOX(main_vbox), clistScrolledWindow, TRUE, TRUE, 0);

	gtk_widget_show(clist);
	gtk_widget_show(clistScrolledWindow);

	questMark.pixmap = gdk_pixmap_create_from_xpm_d(GUI_SpeedDialWindow->window,
							&questMark.mask,
							&GUI_SpeedDialWindow->style->
							bg[GTK_STATE_NORMAL], quest_xpm);

	CreateErrorDialog(&errorDialog, GUI_SpeedDialWindow);
	CreateInfoDialog(&infoDialog, GUI_SpeedDialWindow);
	speedDialInitialized = FALSE;
}
