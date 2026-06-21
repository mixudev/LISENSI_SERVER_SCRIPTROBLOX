const { SlashCommandBuilder, ModalBuilder, TextInputBuilder, TextInputStyle, ActionRowBuilder } = require('discord.js');
const { errorEmbed } = require('../../utils/replyHelper');

const FOCUS_MODAL_ID = 'focus_modal_start';
const FOCUS_INPUT_DURATION_ID = 'focus_input_duration';
const FOCUS_INPUT_BREAK_ID = 'focus_input_break';

module.exports = {
  data: new SlashCommandBuilder()
    .setName('focus')
    .setDescription('Memulai sesi Focus Timer di Voice Channel.'),

  /**
   * @param {import('discord.js').ChatInputCommandInteraction} interaction
   */
  async execute(interaction) {
    const voiceChannel = interaction.member.voice.channel;

    // 1. Validasi Voice Channel
    if (!voiceChannel) {
      await interaction.reply({
        embeds: [errorEmbed('Anda harus bergabung ke Voice Channel terlebih dahulu untuk menggunakan Focus Timer!')],
        ephemeral: true,
      });
      return;
    }

    // 2. Buat & Tampilkan Modal
    const modal = new ModalBuilder()
      .setCustomId(FOCUS_MODAL_ID)
      .setTitle('⏱️ Konfigurasi Focus Timer');

    const focusInput = new TextInputBuilder()
      .setCustomId(FOCUS_INPUT_DURATION_ID)
      .setLabel('Durasi Waktu Fokus (Menit)')
      .setValue('25')
      .setPlaceholder('Contoh: 25')
      .setStyle(TextInputStyle.Short)
      .setMinLength(1)
      .setMaxLength(3)
      .setRequired(true);

    const breakInput = new TextInputBuilder()
      .setCustomId(FOCUS_INPUT_BREAK_ID)
      .setLabel('Durasi Istirahat (Menit)')
      .setValue('5')
      .setPlaceholder('Contoh: 5')
      .setStyle(TextInputStyle.Short)
      .setMinLength(1)
      .setMaxLength(3)
      .setRequired(true);

    modal.addComponents(
      new ActionRowBuilder().addComponents(focusInput),
      new ActionRowBuilder().addComponents(breakInput)
    );

    await interaction.showModal(modal);
  },
};

module.exports.FOCUS_MODAL_ID = FOCUS_MODAL_ID;
module.exports.FOCUS_INPUT_DURATION_ID = FOCUS_INPUT_DURATION_ID;
module.exports.FOCUS_INPUT_BREAK_ID = FOCUS_INPUT_BREAK_ID;
