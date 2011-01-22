/*

  $Id$

  G N O K I I

  A Linux/Unix toolset and driver for the mobile phones.

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

  Copyright (C) 2009 Daniele Forsi

  This file provides functions specific to at commands on Huawei phones.
  See README for more details on supported mobile phones.

*/

#include "config.h"
#include "phones/atgen.h"
#include "phones/athuawei.h"

void at_huawei_init(char* foundmodel, char* setupmodel, struct gn_statemachine *state)
{
	/*
	 * Affected phones: Huawei E172 (E17X according to --identify)
	 *
	 * AT+CNMI=2,1 is supported but +CMTI notifications are sent only with
	 * AT+CNMI=1,1 and only on the second port (eg. /dev/ttyUSB1 not /dev/ttyUSB0)
	 */
	AT_DRVINST(state)->cnmi_mode = 1;
}
